<?php

namespace AppBundle\Referent;

use AppBundle\Entity\Adherent;
use AppBundle\History\AdherentEmailSubscriptionHistoryManager;
use AppBundle\Repository\ReferentTagRepository;

class ReferentTagManager
{
    private $referentTagRepository;
    private $emailSubscriptionHistoryManager;

    public function __construct(ReferentTagRepository $referentTagRepository, AdherentEmailSubscriptionHistoryManager $emailSubscriptionHistoryManager)
    {
        $this->referentTagRepository = $referentTagRepository;
        $this->emailSubscriptionHistoryManager = $emailSubscriptionHistoryManager;
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
}
