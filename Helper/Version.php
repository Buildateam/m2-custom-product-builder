<?php

namespace Buildateam\CustomProductBuilder\Helper;

use Buildateam\CustomProductBuilder\Model\Source\FileLocation;
use Buildateam\CustomProductBuilder\Model\Source\Mode;
use Magento\Framework\App\Filesystem\DirectoryList;
use ZipArchive;

class Version extends Data
{
    const CPB_DEV_HOST = 'https://dev-customproductbuilder.buildateam.io/dist/';
    const CPB_PROD_HOST = 'https://dev-customproductbuilder.buildateam.io/dist/';
    const CPB_DEV_SCRIPT_LINK = self::CPB_DEV_HOST . 'custom-product-builder.js';
    const CPB_PROD_SCRIPT_LINK = self::CPB_PROD_HOST . 'custom-product-builder.js';

    protected $_scripts = [
        'builder-app-bundle.css',
        'builder-app-bundle.js',
        'builder-global-bundle.css',
        'builder-global-bundle.js',
        'custom-product-builder.js',
        'customizer-theme-main.css',
        'customizer-theme-main.js'
    ];

    /**
     * @return string
     */
    public function isActualScriptVersion()
    {
        if ($this->getFileLocation() == FileLocation::LOCATION_REMOTE) {
            return true;
        }

        $localScriptPath = $this->_assetRepo->createAsset('Buildateam_CustomProductBuilder::js/' . $this->getBuilderMode() . '/dist/custom-product-builder.js');
        return md5($localScriptPath->getContent()) == md5(file_get_contents($this->getBuilderMode() == Mode::MODE_DEVELOP ? self::CPB_DEV_SCRIPT_LINK : self::CPB_PROD_SCRIPT_LINK));
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getUpdateArchive()
    {
        $varDir = $this->_fileSystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $fileDir = $varDir->getAbsolutePath() . 'tmp';


        $zip = new ZipArchive();
        $archiveName = 'scripts.zip';
        $archive = $fileDir . DIRECTORY_SEPARATOR . $archiveName;
        if (file_exists($archive)) {
            unlink($archive);
        }
        $res = $zip->open($archive, ZipArchive::CREATE);
        if ($res !== true) {
            return false;
        }

        foreach ($this->_scripts as $scriptName) {
            $content = @file_get_contents($this->_getRemoteHost() . $scriptName);
            $varDir->writeFile($fileDir . DIRECTORY_SEPARATOR . $scriptName, $content);
            try {
                $zip->addFile($fileDir . DIRECTORY_SEPARATOR . $scriptName, $scriptName);
            } catch (\Exception $e) {
                return false;
            }
        }

        $zip->close();

        return $archive;
    }

    /**
     * @return string
     */
    protected function _getRemoteHost()
    {
        switch ($this->getBuilderMode()) {
            case Mode::MODE_DEVELOP:
                return self::CPB_DEV_HOST;
            case Mode::MODE_PRODUCTION:
            default:
                return self::CPB_PROD_HOST;
        }
    }
}