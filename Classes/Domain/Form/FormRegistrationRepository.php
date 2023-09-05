<?php

/*
 * This file is part of the Sitegeist.StoneTablet package.
 */

declare(strict_types=1);

namespace Sitegeist\StoneTablet\Domain\Form;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Neos\Flow\Annotations as Flow;

#[Flow\Scope('singleton')]
final class FormRegistrationRepository
{
    private const TABLE_NAME = 'sitegeist_stonetablet_form_registration';
    public const DATE_FORMAT = 'Y-m-d H:i:s';

    #[Flow\Inject]
    protected EntityManagerInterface $entityManager;

    public function add(FormRegistration $formRegistration): void
    {
        $this->getDatabaseConnection()->insert(
            self::TABLE_NAME,
            [
                'identifier' => $formRegistration->identifier,
                'form_id' => $formRegistration->formId,
                'form_data' => json_encode($formRegistration->formData),
                'recorded_at' => $formRegistration->recordedAt->format(self::DATE_FORMAT)
            ]
        );
    }
    public function findAll(): FormRegistrations
    {
        $rows = $this->getDatabaseConnection()->executeQuery(
            'SELECT * FROM ' . self::TABLE_NAME
            . ' ORDER BY recorded_at ASC'
        )->fetchAllAssociative();

        return new FormRegistrations(
            ...array_map(function (array $row) {
                return $this->mapRowToFormRegistration($row);
            }, $rows)
        );
    }

    /**
     * @return FormRegistrations
     */
    public function findByExportQuery(
        FormRegistrationExportQuery $query
    ): FormRegistrations {
        $rows = $this->getDatabaseConnection()->executeQuery(
            'SELECT * FROM ' . self::TABLE_NAME
            . ' WHERE recorded_at >= :startDate
                AND recorded_at < :endDate
                AND form_id = :formId
                ORDER BY recorded_at ASC',
            [
                'startDate' => $query->startDate->format(self::DATE_FORMAT),
                'endDate' => $query->endDate->format(self::DATE_FORMAT),
                'formId' => $query->formId
            ]
        )->fetchAllAssociative();

        return new FormRegistrations(
            ...array_map(function (array $row) {
                return $this->mapRowToFormRegistration($row);
            }, $rows)
        );
    }

    /**
     * @param array<string,mixed> $row
     */
    private function mapRowToFormRegistration(array $row): FormRegistration
    {
        return new FormRegistration(
            $row['identifier'],
            $row['form_id'],
            json_decode($row['form_data'], true),
            \DateTimeImmutable::createFromFormat(self::DATE_FORMAT, $row['recorded_at'])
                ? : new \DateTimeImmutable()
        );
    }

    private function getDatabaseConnection(): Connection
    {
        return $this->entityManager->getConnection();
    }
}
