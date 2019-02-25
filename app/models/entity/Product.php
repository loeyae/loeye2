<?php

namespace app\models\entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Product
 *
 * @ORM\Table(name="product", indexes={@ORM\Index(name="type", columns={"type"}), @ORM\Index(name="name", columns={"name", "created"})})
 * @ORM\Entity
 */
class Product
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", options={"comment"="ID"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="type", type="integer", nullable=false, options={"comment"="分类"})
     */
    private $type;

    /**
     * @var string|null
     *
     * @ORM\Column(name="name", type="string", length=32, nullable=true, options={"comment"="名称"})
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(name="created", type="integer", nullable=false, options={"unsigned"=true,"comment"="生成时间"})
     */
    private $created;

    /**
     * @var string|null
     *
     * @ORM\Column(name="content", type="text", length=0, nullable=true)
     */
    private $content;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="utime", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $utime = 'CURRENT_TIMESTAMP';


    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set type.
     *
     * @param int $type
     *
     * @return Product
     */
    public function setType($type)
    {
        $this->type = $type;
    
        return $this;
    }

    /**
     * Get type.
     *
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set name.
     *
     * @param string|null $name
     *
     * @return Product
     */
    public function setName($name = null)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name.
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set created.
     *
     * @param int $created
     *
     * @return Product
     */
    public function setCreated($created)
    {
        $this->created = $created;
    
        return $this;
    }

    /**
     * Get created.
     *
     * @return int
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set content.
     *
     * @param string|null $content
     *
     * @return Product
     */
    public function setContent($content = null)
    {
        $this->content = $content;
    
        return $this;
    }

    /**
     * Get content.
     *
     * @return string|null
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set utime.
     *
     * @param \DateTime $utime
     *
     * @return Product
     */
    public function setUtime($utime)
    {
        $this->utime = $utime;
    
        return $this;
    }

    /**
     * Get utime.
     *
     * @return \DateTime
     */
    public function getUtime()
    {
        return $this->utime;
    }
}
