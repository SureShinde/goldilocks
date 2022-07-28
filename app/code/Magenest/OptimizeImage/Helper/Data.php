<?php
namespace Magenest\OptimizeImage\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Shell;
use Psr\Log\LoggerInterface;

class Data extends AbstractHelper
{
    /**
     * @var ScopeConfigInterface
     */
    public $scopeConfig;

    /**
     * @var Filesystem
     */
    public $fileSystem;

    /**
     * @var ComponentRegistrarInterface
     */
    public $componentRegistrar;

    /**
     * @var Shell
     */
    public $shell;

    /**
     * @var LoggerInterface
     */
    public $logger;

    /**
     * Magento Root full path.
     *
     * @var null|string
     */
    public $baseDir = null;

    /**
     * Module Root full path.
     *
     * @var null|string
     */
    public $moduleDir = null;

    /**
     * Logging flag.
     *
     * @var null|int
     */
    public $logging = null;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Filesystem $fileSystem
     * @param ComponentRegistrarInterface $compReg
     * @param Shell $shell
     */
    public function __construct(
        Context $context,
        Filesystem $fileSystem,
        ComponentRegistrarInterface $compReg,
        Shell $shell
    ) {
        $this->scopeConfig        = $context->getScopeConfig();
        $this->logger             = $context->getLogger();
        $this->fileSystem         = $fileSystem;
        $this->componentRegistrar = $compReg;
        $this->shell              = $shell;

        parent::__construct($context);
    }

    /**
     * Based on provided configuration path returns configuration value.
     *
     * @param string $configPath
     * @return string
     */
    public function getConfig($configPath)
    {
        return $this->scopeConfig->getValue($configPath);
    }

    public function isEnable()
    {
        return $this->getConfig('magenest_optimizeimage/utility/enabled');
    }

    /**
     * Optimized way of getting logging flag from config.
     *
     * @return int
     */
    public function isLoggingEnabled()
    {
        if ($this->logging === null) {
            $this->logging = (int) $this->getConfig(
                'magenest_optimizeimage/utility/log_output'
            );
        }

        return $this->logging;
    }

    /**
     * Returns Module Root full path.
     *
     * @return null|string
     */
    public function getModuleDir()
    {
        if ($this->moduleDir === null) {
            $moduleName = 'Magenest_OptimizeImage';

            $this->moduleDir = $this->componentRegistrar->getPath(
                ComponentRegistrar::MODULE,
                $moduleName
            );
        }

        return $this->moduleDir;
    }

    /**
     * Optimizes single file.
     *
     * @param string $filePath
     * @return boolean
     */
    public function optimizeFile($filePath)
    {
        $info     = pathinfo($filePath);
        $output   = '';
        $exitCode = 0;

        try {
            switch (strtolower($info['extension'])) {
                case 'jpg':
                case 'jpeg':
                    $output = $this->shell
                        ->execute($this->getJpgUtil($filePath));
                    break;
                case 'png':
                    $output = $this->shell
                        ->execute($this->getPngUtil($filePath));
                    break;
            }
        } catch (LocalizedException $e) {
            $this->logger->debug($e->getMessage());
            $this->logger->debug($e->getPrevious()->getMessage());

            $exitCode = $e->getPrevious()->getCode();

            $this->logger->debug('Exit code: ' . $exitCode);

            if ($exitCode == 126) {
                $error = 'Image optimization utility is not executable.';

                $this->logger->debug($error);
            }

            return false;
        }

        if ($this->isLoggingEnabled()) {
            $this->logger->debug($filePath);
            $this->logger->debug($output);
        }

        return true;
    }

    /**
     * Alias for getUtil() and .jpg
     *
     * @param string $filePath
     * @return string
     */
    public function getJpgUtil($filePath)
    {
        return $this->getUtil('jpg', $filePath);
    }

    /**
     * Alias for getUtil() and .png
     *
     * @param string $filePath
     * @return string
     */
    public function getPngUtil($filePath)
    {
        return $this->getUtil('png', $filePath);
    }

    /**
     * Formats and returns the shell command string for an image optimization
     * utility.
     *
     * @param string $type - This is image type. Valid values jpg|png
     * @param string $filePath - Path to the image to be optimized
     * @return string
     */
    public function getUtil($type, $filePath)
    {
        $exactPath = $this->getConfig(
            'magenest_optimizeimage/utility/' . $type . '_path'
        );

        // If utility exact path is set use it
        if ($exactPath != '') {
            $cmd = $exactPath;
        // Use path to extension's local utilities
        } else {
            $cmd = $this->getModuleDir()
                . '/bin/linux64/'
                . $this->getConfig('magenest_optimizeimage/utility/' . $type);
        }

        $cmd .= ' ' . $this->getConfig(
            'magenest_optimizeimage/utility/' . $type . '_options'
        );

        return str_replace('%filepath%', $filePath, $cmd);
    }
}
