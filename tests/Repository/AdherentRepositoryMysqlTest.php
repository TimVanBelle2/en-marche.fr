<?php

namespace Tests\AppBundle\Repository;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadReferentTagData;
use AppBundle\Entity\CitizenProject;
use AppBundle\Entity\Committee;
use AppBundle\Entity\ReferentTag;
use AppBundle\Repository\AdherentRepository;
use AppBundle\Repository\ReferentTagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\MysqlWebTestCase;

/**
 * @group functional
 */
class AdherentRepositoryMysqlTest extends MysqlWebTestCase
{
    /**
     * @var AdherentRepository
     */
    private $adherentRepository;

    /**
     * @var ReferentTagRepository
     */
    private $referentTagRepository;

    use ControllerTestTrait;

    public function testFindReferentsByCommittee()
    {
        $committeeTags = new ArrayCollection([
            $this->referentTagRepository->findOneByCode('CH')
        ]);

        // Foreign Committee with Referent
        $committee = $this->createMock(Committee::class);
        $committee->expects(static::any())->method('getReferentTags')->willReturn($committeeTags);

        $referents = $this->adherentRepository->findReferentsByCommittee($committee);

        $this->assertNotEmpty($referents);
        $this->assertCount(2, $referents);

        $referent = $referents->first();

        $this->assertSame('Referent Referent', $referent->getFullName());
        $this->assertSame('referent@en-marche-dev.fr', $referent->getEmailAddress());

        // Committee with no Referent
        $committeeTags = new ArrayCollection([
            $this->referentTagRepository->findOneByCode('44')
        ]);

        $committee = $this->createMock(Committee::class);
        $committee->expects(static::any())->method('getReferentTags')->willReturn($committeeTags);

        $referents = $this->adherentRepository->findReferentsByCommittee($committee);

        $this->assertEmpty($referents);

        // Departemental Commitee with Referent
        $committeeTags = new ArrayCollection([
            $this->referentTagRepository->findOneByCode('77')
        ]);

        $committee = $this->createMock(Committee::class);
        $committee->expects(static::any())->method('getReferentTags')->willReturn($committeeTags);

        $referents = $this->adherentRepository->findReferentsByCommittee($committee);

        $this->assertCount(1, $referents);

        $referent = $referents->first();

        $this->assertSame('Referent Referent', $referent->getFullName());
        $this->assertSame('referent@en-marche-dev.fr', $referent->getEmailAddress());
    }

    public function testFindCoordinatorsByCitizenProject()
    {
        // Foreign Citizen Project with Coordinator
        $citizenProject = $this->createMock(CitizenProject::class);
        $citizenProject->expects(static::any())->method('getCountry')->willReturn('US');

        $coordinators = $this->adherentRepository->findCoordinatorsByCitizenProject($citizenProject);

        $this->assertNotEmpty($coordinators);
        $this->assertCount(1, $coordinators);

        $coordinator = $coordinators->first();

        $this->assertSame('Coordinatrice CITIZEN PROJECT', $coordinator->getFullName());
        $this->assertSame('coordinatrice-cp@en-marche-dev.fr', $coordinator->getEmailAddress());

        // Citizen Project with no Coordinator
        $citizenProject = $this->createMock(CitizenProject::class);
        $citizenProject->expects(static::any())->method('getCountry')->willReturn('FR');
        $citizenProject->expects(static::any())->method('getPostalCode')->willReturn('59000');

        $coordinators = $this->adherentRepository->findCoordinatorsByCitizenProject($citizenProject);

        $this->assertEmpty($coordinators);

        // Departemental Citizen Project with Coordinator
        $citizenProject = $this->createMock(CitizenProject::class);
        $citizenProject->expects(static::any())->method('getCountry')->willReturn('FR');
        $citizenProject->expects(static::any())->method('getPostalCode')->willReturn('77500');

        $coordinators = $this->adherentRepository->findCoordinatorsByCitizenProject($citizenProject);

        $this->assertCount(1, $coordinators);

        $coordinator = $coordinators->first();

        $this->assertSame('Coordinatrice CITIZEN PROJECT', $coordinator->getFullName());
        $this->assertSame('coordinatrice-cp@en-marche-dev.fr', $coordinator->getEmailAddress());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->loadFixtures([
            LoadAdherentData::class,
            LoadReferentTagData::class,
        ]);

        $this->container = $this->getContainer();
        $this->adherentRepository = $this->getAdherentRepository();
        $this->referentTagRepository = $this->getRepository(ReferentTag::class);
    }

    protected function tearDown()
    {
        $this->loadFixtures([]);

        $this->adherentRepository = null;
        $this->referentTagRepository = null;
        $this->container = null;

        parent::tearDown();
    }
}
