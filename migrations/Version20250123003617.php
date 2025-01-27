<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250123003617 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create transactions table and modify balances table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE transactions (
            id SERIAL NOT NULL, 
            ledger_id INT NOT NULL, 
            balance_id INT NOT NULL, 
            type VARCHAR(255) NOT NULL, 
            amount NUMERIC(15, 2) NOT NULL, 
            transaction_id VARCHAR(255) NOT NULL, 
            created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, 
            PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EAA81A4C2FC0CB0F ON transactions (transaction_id)');
        $this->addSql('CREATE INDEX IDX_EAA81A4CA7B913DD ON transactions (ledger_id)');
        $this->addSql('CREATE INDEX IDX_EAA81A4CAE91A3DD ON transactions (balance_id)');
        $this->addSql('ALTER TABLE transactions ADD CONSTRAINT FK_EAA81A4CA7B913DD FOREIGN KEY (ledger_id) REFERENCES "ledger" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE transactions ADD CONSTRAINT FK_EAA81A4CAE91A3DD FOREIGN KEY (balance_id) REFERENCES balances (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE balances DROP CONSTRAINT IF EXISTS unique_ledger_currency');
        $this->addSql('ALTER TABLE balances ADD CONSTRAINT unique_ledger_currency UNIQUE (ledger_id, currency)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE transactions DROP CONSTRAINT FK_EAA81A4CA7B913DD');
        $this->addSql('ALTER TABLE transactions DROP CONSTRAINT FK_EAA81A4CAE91A3DD');
        $this->addSql('DROP TABLE transactions');
        $this->addSql('ALTER TABLE balances DROP CONSTRAINT unique_ledger_currency');
        $this->addSql('CREATE UNIQUE INDEX balances_ledger_id_currency_key ON balances (ledger_id, currency)');
    }
}
