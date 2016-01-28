<?php

namespace Voelkel\DataTablesBundle\Tests\DataTables;

use Voelkel\DataTablesBundle\DataTables\ServerSide;

class ServerSideTest extends \PHPUnit_Framework_TestCase
{
    public function testProcessRequest()
    {
        $countQuery = $this->getMockBuilder('Doctrine\ORM\AbstractQuery')
            ->disableOriginalConstructor()
            ->getMock();
        $countQuery->expects($this->exactly(1))
            ->method('getSingleScalarResult')
            ->will($this->returnValue(1));


        $user1 = new \Voelkel\DataTablesBundle\Tests\DataTables\Entity\TestUser();
        $user1->setId(4711)
            ->setName('Testuser 1')
            ->setStatus(123);

        $group1 = new \Voelkel\DataTablesBundle\Tests\DataTables\Entity\TestGroup();
        $group1->setId(1);
        $user1->addGroup($group1);

        $group2 = new \Voelkel\DataTablesBundle\Tests\DataTables\Entity\TestGroup();
        $group2->setId(2);
        $user1->addGroup($group2);


        $entityQuery = $this->getMockBuilder('Doctrine\ORM\AbstractQuery')
            ->disableOriginalConstructor()
            ->getMock();
        $entityQuery->expects($this->exactly(1))
            ->method('getResult')
            ->will($this->returnValue([$user1]));

        $queryBuilder = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $queryBuilder->expects($this->exactly(1))
            ->method('leftJoin')
            ->with('u.groups', 'g')
            ->will($this->returnValue($queryBuilder));

        $queryBuilder->expects($this->exactly(2))
            ->method('select')
            ->withConsecutive(
                ['count(u.id)'],
                ['u']
            )
            ->will($this->onConsecutiveCalls(
                null,
                null
            ));
        $queryBuilder->expects($this->exactly(2))
            ->method('getQuery')
            ->will($this->onConsecutiveCalls(
                $countQuery,
                $entityQuery
            ));
        $queryBuilder->expects($this->exactly(1))
            ->method('setFirstResult')
            ->with(25)
            ->will($this->returnValue($queryBuilder));
        $queryBuilder->expects($this->exactly(1))
            ->method('setMaxResults')
            ->with(50)
            ->will($this->returnValue($queryBuilder));



        $repository = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repository->expects($this->exactly(1))
            ->method('createQueryBuilder')
            ->with('u')
            ->will($this->returnValue($queryBuilder));


        $em = $this->getMockBuilder('Doctrine\ORM\EntityManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $em->expects($this->exactly(1))
            ->method('getRepository')
            ->with('Voelkel\DataTablesBundle\Tests\DataTables\Entity\TestUser')
            ->will($this->returnValue($repository));


        $table = new TestTable();

        $sfRequest = new \Symfony\Component\HttpFoundation\Request([
            'draw' => 42,
            'start' => 25,
            'length' => 50,
        ]);

        $serverSide = new ServerSide($em);
        $respone = $serverSide->processRequest($table, $sfRequest);

        $data = json_decode($respone->getContent(), true);
        $this->assertCount(1, $data['data']);

        $row = reset($data['data']);
        $this->assertSame(4711, $row['id']);
        $this->assertSame('*Testuser 1*', $row['name_unbound']);
    }
}
