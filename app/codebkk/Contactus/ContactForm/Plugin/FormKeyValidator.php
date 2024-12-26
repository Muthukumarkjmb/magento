<?php

namespace Contactus\ContactForm\Plugin;

use Magento\Framework\App\Request\Http;

class FormKeyValidator
{
    /**
     * Around plugin to bypass form key validation.
     *
     * @param Http $subject
     * @param callable $proceed
     * @param string $key
     * @param null $default
     * @return mixed
     */
    public function aroundGetParam(Http $subject, callable $proceed, $key, $default = null)
    {
        // Bypass form key validation only if necessary, for example for specific controller or request type
        if ($key == 'form_key') {
            return null; // Return a null value or any other value you prefer to bypass the check
        }
        
        return $proceed($key, $default);
    }
}
