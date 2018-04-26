<?php

namespace AppBundle\Referent;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Repository\ReferentTagRepository;

class ReferentTagManager
{
    private $referentTagRepository;

    public function __construct(ReferentTagRepository $referentTagRepository)
    {
        $this->referentTagRepository = $referentTagRepository;
    }

    public function assignAdherentLocalTag(Adherent $adherent): void
    {
        $adherent->removeReferentTags();

        $codes = ManagedAreaUtils::getCodesFromAdherent($adherent);

        if (empty($codes)) {
            return;
        }

        foreach ($this->referentTagRepository->findByCodes($codes) as $referentTag) {
            $adherent->addReferentTag($referentTag);
        }
    }

    public function assignCommitteeLocalTag(Committee $committee): void
    {
        $committee->removeReferentTags();

        $codes = ManagedAreaUtils::getCodesFromCommittee($committee);

        if (empty($codes)) {
            return;
        }

        foreach ($this->referentTagRepository->findByCodes($codes) as $referentTag) {
            $committee->addReferentTag($referentTag);
        }
    }
}
