<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260501104802 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ensure tables exist before altering them and switch to UUIDs.';
    }

    public function up(Schema $schema): void
    {
        // Ensure tables exist before trying to alter them
        $this->addSql('CREATE TABLE IF NOT EXISTS users (id INT NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) DEFAULT NULL, age INT DEFAULT NULL, bio TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX IF NOT EXISTS UNIQ_1483A5E9F85E0677 ON users (username)');
        
        $this->addSql('CREATE TABLE IF NOT EXISTS photos (id INT NOT NULL, user_id INT NOT NULL, image_url TEXT NOT NULL, location VARCHAR(255) DEFAULT NULL, description TEXT DEFAULT NULL, camera VARCHAR(255) DEFAULT NULL, taken_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, like_counter INT DEFAULT 0 NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IF NOT EXISTS IDX_876E0D9A76ED395 ON photos (user_id)');

        $this->addSql('CREATE TABLE IF NOT EXISTS likes (id INT NOT NULL, user_id INT NOT NULL, photo_id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IF NOT EXISTS IDX_49CA4E7DA76ED395 ON likes (user_id)');
        $this->addSql('CREATE INDEX IF NOT EXISTS IDX_49CA4E7D7E9E4C8C ON likes (photo_id)');

        // Now, alter the tables to use UUIDs
        $this->addSql('ALTER TABLE likes ALTER user_id TYPE UUID USING (user_id::text::uuid)');
        $this->addSql('ALTER TABLE likes ALTER photo_id TYPE UUID USING (photo_id::text::uuid)');
        $this->addSql('COMMENT ON COLUMN likes.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN likes.photo_id IS \'(DC2Type:uuid)\'');
        
        $this->addSql('ALTER TABLE photos ALTER id TYPE UUID USING (id::text::uuid)');
        $this->addSql('ALTER TABLE photos ALTER user_id TYPE UUID USING (user_id::text::uuid)');
        $this->addSql('COMMENT ON COLUMN photos.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN photos.user_id IS \'(DC2Type:uuid)\'');

        $this->addSql('ALTER TABLE users ALTER id TYPE UUID USING (id::text::uuid)');
        $this->addSql('COMMENT ON COLUMN users.id IS \'(DC2Type:uuid)\'');
    }

    public function down(Schema $schema): void
    {
        // ... down migration remains the same
    }
}
