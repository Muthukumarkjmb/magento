<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Model\Import\Validation;

class ValidatorPool implements ValidatorPoolInterface
{
    /**
     * @var \Amasty\Base\Model\Import\Validation\ValidatorInterface[]
     */
    private $validators;

    public function __construct(
        $validators
    ) {
        $this->validators = [];
        foreach ($validators as $validator) {
            if (!($validator instanceof ValidatorInterface)) {
                throw new \Amasty\Base\Exceptions\WrongValidatorInterface();
            }

            $this->validators[] = $validator;
        }
    }

    /**
     * @inheritdoc
     */
    public function getValidators()
    {
        return $this->validators;
    }

    /**
     * @inheritdoc
     */
    public function addValidator($validator)
    {
        if (!($validator instanceof ValidatorInterface)) {
            throw new \Amasty\Base\Exceptions\WrongValidatorInterface();
        }

        $this->validators[] = $validator;
    }
}
