<?php

namespace Doctrine\Tests\ORM\Functional\Ticket;

/**
 * @group DDC-1436
 */
class DDC1436Test extends \Doctrine\Tests\OrmFunctionalTestCase
{
    protected function setUp()
    {
        parent::setUp();

        try {
            $this->_schemaTool->createSchema(
                [
                $this->_em->getClassMetadata(DDC1436Page::class),
                ]
            );
        } catch (\Exception $ignored) {
        }
    }

    public function testIdentityMap()
    {
        // fixtures
        $parent = null;
        for ($i = 0; $i < 3; $i++) {
            $page = new DDC1436Page();
            $page->setParent($parent);
            $this->_em->persist($page);
            $parent = $page;
        }
        $this->_em->flush();
        $this->_em->clear();

        $id = $parent->getId();

        // step 1
        $page = $this->_em
                ->createQuery('SELECT p, parent FROM ' . __NAMESPACE__ . '\DDC1436Page p LEFT JOIN p.parent parent WHERE p.id = :id')
                ->setParameter('id', $id)
                ->getOneOrNullResult();

        $this->assertInstanceOf(DDC1436Page::class, $page);

        // step 2
        $page = $this->_em->find(DDC1436Page::class, $id);
        $this->assertInstanceOf(DDC1436Page::class, $page);
        $this->assertInstanceOf(DDC1436Page::class, $page->getParent());
        $this->assertInstanceOf(DDC1436Page::class, $page->getParent()->getParent());
    }
}

/**
 * @Entity
 */
class DDC1436Page
{
    /**
     * @Id
     * @GeneratedValue
     * @Column(type="integer", name="id")
     */
    protected $id;
    /**
     * @ManyToOne(targetEntity="DDC1436Page")
     * @JoinColumn(name="pid", referencedColumnName="id")
     */
    protected $parent;

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return DDC1436Page
     */
    public function getParent()
    {
        return $this->parent;
    }

    public function setParent($parent)
    {
        $this->parent = $parent;
    }
}

