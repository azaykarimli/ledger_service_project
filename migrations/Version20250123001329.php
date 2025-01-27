<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250123001329 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE balances (
            id SERIAL NOT NULL, 
            ledger_id INT NOT NULL, 
            currency VARCHAR(3) NOT NULL, 
            balance NUMERIC(15, 2) DEFAULT 0 NOT NULL, 
            updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, 
            PRIMARY KEY(id),
            UNIQUE (ledger_id, currency)
        )');
        $this->addSql('CREATE INDEX IDX_41A7E40FA7B913DD ON balances (ledger_id)');
        $this->addSql('ALTER TABLE balances ADD CONSTRAINT FK_41A7E40FA7B913DD FOREIGN KEY (ledger_id) REFERENCES "ledger" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
    
    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE balances DROP CONSTRAINT FK_41A7E40FA7B913DD');
        $this->addSql('DROP TABLE balances');
    }
}
