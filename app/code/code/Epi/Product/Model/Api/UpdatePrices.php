<?php
namespace Epi\Product\Model\Api;

use \Exception as Exception;
use Magento\Framework\Exception\NotFoundException as NotFoundException;
use Magento\Framework\Exception\InvalidArgumentException as InvalidArgumentException;
use Magento\Framework\Exception\RunTimeException as RuntimeException;
use Magento\Framework\Exception\NoSuchEntityException as NoSuchEntityException;
use \Epi\Product\Model\PricesUpdateResponse;
use \Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Filesystem;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Archive\Zip;
use Magento\Framework\Filesystem\Directory\WriteInterface;


class UpdatePrices{
 
    public const FILE_TYPE = 'application/zip';
    /** @var Http */
    private $request;
    /** @var Filesystem */
    private $filesystem;
    /** @var UploaderFactory */
    private $uploaderFactory;
    /** @var WriteInterface */
    private $varDirectory;
    protected $_file;

    public function __construct(        
        \Zend\Http\Client $zendClient,
        Zip $zip,
        Http $request,
        Filesystem $filesystem,
        File $file,
        UploaderFactory $uploaderFactory
    )
    {
        $ds = DIRECTORY_SEPARATOR;    
        $this->zendClient = $zendClient;
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/ProductApi.log');
        $this->logger = new \Zend_Log();
        $this->logger->addWriter($writer); 
        $this->ini = parse_ini_file(__DIR__ ."$ds..$ds..$ds/lib/config.ini");
        $this->_file =$file;
        $this->response = new PricesUpdateResponse();
        $this->zip = $zip;
        $this->request = $request;
        $this->filesystem = $filesystem;
        $this->uploaderFactory = $uploaderFactory;
        $this->varDirectory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
    }

    private function saveFile()
    {
        $uploader = $this->uploaderFactory->create(['fileId' => 'file']);
        $workingDir = $this->varDirectory->getAbsolutePath('webapidocuments/');
        $uploader->setAllowedExtensions(['zip']);
        $uploader->setAllowRenameFiles(true); 
        $result = $uploader->save($workingDir);
        $this->logger->info("file content".print_r($result,true));
        return $workingDir . $uploader->getUploadedFileName();
    }   
    private function validateFile($zipFilePath){
        $zip = new \ZipArchive;
        $zip->open($zipFilePath);
        if($zip->count()!=1){
            $this->_file->deleteFile($zipFilePath);
            throw new InvalidArgumentException(__("Only 1 file expected inside zip"));
        }
        $stat = $zip->statIndex( 0 ); 
        $fileName=basename($stat['name']);
        $n = strrpos($fileName,".");
        if($n!=false){
            $extension=substr($fileName,$n+1);
            $this->logger->info("Extension".$extension);
            if($extension!="json"){
                $this->_file->deleteFile($zipFilePath);
                throw new InvalidArgumentException(__("Invalid file type inside zip file. Expected json file"));
            }
        }
    } 
    public function updatePrices() {
        $this->logger->info('<----- Update Product ----->');
        try {
            $fileInfo = $this->request->getFiles('file');
            $this->logger->info("Data coming".print_r($fileInfo,true));
            $zipFilePath=$this->saveFile();
            $this->validateFile($zipFilePath);
            $this->logger->info("file name".print_r($zipFilePath,true));
            $unzipFilePath = $this->varDirectory->getAbsolutePath('webapidocuments/ItemsPrice.json');
            $extractedFile=$this->zip->unpack($zipFilePath,$unzipFilePath);
            $this->logger->info("Extracted file name".print_r($extractedFile,true));
            $fileContents = file_get_contents($extractedFile); 
            $parsedData = json_decode($fileContents, true);
            $itemsData=$parsedData['getallpricesresult']['items']['item']?$parsedData['getallpricesresult']['items']['item']:[];
            // $this->logger->info("File content".print_r($parsedData['getallpricesresult']['items']['item'],true));
           if(count($itemsData)==0){
            $this->_file->deleteFile($zipFilePath);
            throw new InvalidArgumentException(__("No Data available to update"));
           }    
           
           $filteredItemData=[];
           foreach($itemsData as $data){          
                if(!isset($data['upc']) || !isset($data['currentprice'])){
                    $this->_file->deleteFile($zipFilePath);
                    throw new InvalidArgumentException(__("Invalid data"));
                }
                if(strlen($data['upc'])==0 || strlen($data['currentprice'])==0){
                    $this->_file->deleteFile($zipFilePath);
                    throw new InvalidArgumentException(__("One or more empty data found"));
                }
                array_push($filteredItemData,(object)["sku"=>$data['upc'],"newPrice"=>$data['currentprice']]);
           }     
           $stream=$this->varDirectory->openFile($extractedFile, 'w+');
           $stream->lock();
           $stream->write(json_encode(["items"=>$filteredItemData]));
           $stream->unlock();
           $stream->close();
           $this->response->setSuccess(true); 
           $this->response->setMessage("File successfully saved. You will recieve an email once prices are updated");       
           return $this->response;  
        } catch (\Exception $e) { 
            throw($e);
        }
    }
}