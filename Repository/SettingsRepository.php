<?php

namespace Kotaru\SuluUtils\Repository;

use Kotaru\SuluUtils\Entity\Setting;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Setting>
 *
 * @method Setting|null find($id, $lockMode = null, $lockVersion = null)
 * @method Setting|null findOneBy(array $criteria, array $orderBy = null)
 * @method Setting[]    findAll()
 * @method Setting[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SettingsRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Setting::class);
    }

    public function findByKey(string $key): ?Setting
    {
        $setting = $this->createQueryBuilder('setting')
            ->where('setting.settingKey = :settingKey')
            ->setParameter('settingKey', $key)
            ->getQuery()->getOneOrNullResult();
        if (!$setting instanceof Setting) {
            return null;
        }
        return $setting;
    }

    public function create(?string $key = null): Setting
    {
        $setting = new Setting();
        if (isset($key)) {
            $setting->setKey($key);
        }
        return $setting;
    }


    public function save(Setting $setting): void
    {
        $em = $this->getEntityManager();
        $em->persist($setting);
    }

    public function detach(Setting $setting): void
    {
        $em = $this->getEntityManager();
        $em->detach($setting);
    }
    public function flush(): void
    {
        $em = $this->getEntityManager();
        $em->flush();
    }

    public function remove(int $id): void
    {
        $em = $this->getEntityManager();

        $setting = $this->find($id);

        if (!$setting instanceof Setting) {
            return;
        }

        $em->remove($setting);
    }


}
