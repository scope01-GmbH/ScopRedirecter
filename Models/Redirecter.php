<?php

namespace ScopRedirecter\Models;

use Shopware\Components\Model\ModelEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="s_plugin_redirecter")
 * @ORM\Entity(repositoryClass="ScopRedirecter\Models\ScopRedirecterRepository")
 */
class Redirecter extends ModelEntity
{

    //COLUMN DEFINITIONS:
    /**
     * Primary Key - autoincrement value
     *
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string $startUrl
     *
     * @ORM\Column(name="start_url", type="string", nullable=false, unique=true)
     */
    private $startUrl;

    /**
     * @var string $targetUrl
     *
     * @ORM\Column(name="target_url", type="string", nullable=false)
     */
    private $targetUrl;

    /**
     * @var integer $httpCode
     *
     * @ORM\Column(name="http_code", type="integer", nullable=true)
     */
    private $httpCode;


    // ID FUNCTIONS:

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


    //START URL FUNCTIONS:

    /**
     * @return string
     */
    public function getStartUrl()
    {
        return $this->startUrl;
    }

    /**
     * @param string $startUrl
     */
    public function setStartUrl($startUrl)
    {
        $this->startUrl = $startUrl;
    }


    //TARGET URL FUNCTIONS:

    /**
     * @return string
     */
    public function getTargetUrl()
    {
        return $this->targetUrl;
    }

    /**
     * @param string $targetUrl
     */
    public function setTargetUrl($targetUrl)
    {
        $this->targetUrl = $targetUrl;
    }

    //HTTTP CODE FUNCTIONS:

    /**
     * @return integer
     */
    public function getHttpCode()
    {
        return $this->httpCode;
    }

    /**
     * @param integer $httpCode
     */
    public function setHttpCode($httpCode)
    {
        $this->httpCode = $httpCode;
    }
}