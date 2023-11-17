<?php

namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration to add tables for stone tablet form registration
 */
class Version20231106184042 extends AbstractMigration
{
    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Creates tables for contact form registrations';
    }

    /**
     * @param Schema $schema
     * @return void
     * @throws \Doctrine\DBAL\Exception
     */
    public function up(Schema $schema): void
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != 'mysql',
            'Migration can only be executed safely on "mysql".'
        );

        $this->addSql('CREATE TABLE `sitegeist_stonetablet_form_registration` (
                `identifier` VARCHAR(40) NOT NULL,
                `form_id` VARCHAR(40) NOT NULL,
                `form_data` TEXT,
                `recorded_at` datetime NOT NULL,
                PRIMARY KEY(`identifier`))
                DEFAULT CHARACTER SET utf8mb4
                COLLATE utf8mb4_unicode_ci
                ENGINE = InnoDB');
    }

    /**
     * @param Schema $schema
     * @return void
     * @throws \Doctrine\DBAL\Exception
     */
    public function down(Schema $schema): void
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != 'mysql',
            'Migration can only be executed safely on "mysql".'
        );

        $this->addSql('DROP TABLE sitegeist_stonetablet_form_registration');
    }
}
