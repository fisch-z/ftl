<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240719010638 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE battalion (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, custom_name VARCHAR(255) DEFAULT NULL, sort INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE billet_assignment (id INT AUTO_INCREMENT NOT NULL, section_id INT NOT NULL, position_id INT NOT NULL, milpac_id INT NOT NULL, milpac_title VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_277691C6D774F8AF (milpac_id), INDEX IDX_277691C6D823E37A (section_id), INDEX IDX_277691C6DD842E46 (position_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE billet_position (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, sort INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE company (id INT AUTO_INCREMENT NOT NULL, battalion_id INT NOT NULL, title VARCHAR(255) NOT NULL, custom_name VARCHAR(255) DEFAULT NULL, sort INT NOT NULL, INDEX IDX_4FBF094F5A3C6E93 (battalion_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE milpac_profile (id INT AUTO_INCREMENT NOT NULL, primary_billet_assignment_id INT NOT NULL, rank_id INT NOT NULL, user_id INT NOT NULL, username VARCHAR(255) NOT NULL, roster_type VARCHAR(255) NOT NULL, data JSON NOT NULL COMMENT \'(DC2Type:json)\', synced_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', milpac_data_change_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', uniform_replaced_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', keycloak_id VARCHAR(255) NOT NULL, forum_profile_id INT NOT NULL, joined_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_7B76634DA76ED395 (user_id), UNIQUE INDEX UNIQ_7B76634DF85E0677 (username), INDEX IDX_7B76634DF4FF7106 (primary_billet_assignment_id), INDEX IDX_7B76634D7616678F (rank_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE milpac_profile_billet_assignment (milpac_profile_id INT NOT NULL, billet_assignment_id INT NOT NULL, INDEX IDX_D6D53288E9B537B5 (milpac_profile_id), INDEX IDX_D6D53288225F40B7 (billet_assignment_id), PRIMARY KEY(milpac_profile_id, billet_assignment_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE milpac_profile_uniform_override (id INT AUTO_INCREMENT NOT NULL, milpac_profile_id INT NOT NULL, service_branch_id INT DEFAULT NULL, preferred_primary_special_skill_service_branch_id INT DEFAULT NULL, preferred_secondary_special_skill1 VARCHAR(255) DEFAULT NULL, preferred_secondary_special_skill2 VARCHAR(255) DEFAULT NULL, preferred_secondary_special_skill3 VARCHAR(255) DEFAULT NULL, preferred_crest VARCHAR(255) DEFAULT NULL, preferred_badge VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_8F4EC157E9B537B5 (milpac_profile_id), INDEX IDX_8F4EC1578447F603 (service_branch_id), INDEX IDX_8F4EC157B5EA5AE0 (preferred_primary_special_skill_service_branch_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE platoon (id INT AUTO_INCREMENT NOT NULL, company_id INT NOT NULL, title VARCHAR(255) NOT NULL, custom_name VARCHAR(255) DEFAULT NULL, sort INT NOT NULL, INDEX IDX_45D91E68979B1AD6 (company_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE rank (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, title_short VARCHAR(255) NOT NULL, sort INT NOT NULL, rank_image_url VARCHAR(1000) DEFAULT NULL, rank_type VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE section (id INT AUTO_INCREMENT NOT NULL, platoon_id INT NOT NULL, service_branch_id INT NOT NULL, title VARCHAR(255) NOT NULL, custom_name VARCHAR(255) DEFAULT NULL, sort INT NOT NULL, INDEX IDX_2D737AEFBCB0A42 (platoon_id), INDEX IDX_2D737AEF8447F603 (service_branch_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE service_branch (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE billet_assignment ADD CONSTRAINT FK_277691C6D823E37A FOREIGN KEY (section_id) REFERENCES section (id)');
        $this->addSql('ALTER TABLE billet_assignment ADD CONSTRAINT FK_277691C6DD842E46 FOREIGN KEY (position_id) REFERENCES billet_position (id)');
        $this->addSql('ALTER TABLE company ADD CONSTRAINT FK_4FBF094F5A3C6E93 FOREIGN KEY (battalion_id) REFERENCES battalion (id)');
        $this->addSql('ALTER TABLE milpac_profile ADD CONSTRAINT FK_7B76634DF4FF7106 FOREIGN KEY (primary_billet_assignment_id) REFERENCES billet_assignment (id)');
        $this->addSql('ALTER TABLE milpac_profile ADD CONSTRAINT FK_7B76634D7616678F FOREIGN KEY (rank_id) REFERENCES rank (id)');
        $this->addSql('ALTER TABLE milpac_profile_billet_assignment ADD CONSTRAINT FK_D6D53288E9B537B5 FOREIGN KEY (milpac_profile_id) REFERENCES milpac_profile (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE milpac_profile_billet_assignment ADD CONSTRAINT FK_D6D53288225F40B7 FOREIGN KEY (billet_assignment_id) REFERENCES billet_assignment (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE milpac_profile_uniform_override ADD CONSTRAINT FK_8F4EC157E9B537B5 FOREIGN KEY (milpac_profile_id) REFERENCES milpac_profile (id)');
        $this->addSql('ALTER TABLE milpac_profile_uniform_override ADD CONSTRAINT FK_8F4EC1578447F603 FOREIGN KEY (service_branch_id) REFERENCES service_branch (id)');
        $this->addSql('ALTER TABLE milpac_profile_uniform_override ADD CONSTRAINT FK_8F4EC157B5EA5AE0 FOREIGN KEY (preferred_primary_special_skill_service_branch_id) REFERENCES service_branch (id)');
        $this->addSql('ALTER TABLE platoon ADD CONSTRAINT FK_45D91E68979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE section ADD CONSTRAINT FK_2D737AEFBCB0A42 FOREIGN KEY (platoon_id) REFERENCES platoon (id)');
        $this->addSql('ALTER TABLE section ADD CONSTRAINT FK_2D737AEF8447F603 FOREIGN KEY (service_branch_id) REFERENCES service_branch (id)');
        $this->addSql('CREATE TABLE section_practice (id INT AUTO_INCREMENT NOT NULL, section_id INT NOT NULL, date_time DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', attendance JSON NOT NULL COMMENT \'(DC2Type:json)\', INDEX IDX_EF816E12D823E37A (section_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE section_practice ADD CONSTRAINT FK_EF816E12D823E37A FOREIGN KEY (section_id) REFERENCES section (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE section_practice DROP FOREIGN KEY FK_EF816E12D823E37A');
        $this->addSql('ALTER TABLE billet_assignment DROP FOREIGN KEY FK_277691C6D823E37A');
        $this->addSql('ALTER TABLE billet_assignment DROP FOREIGN KEY FK_277691C6DD842E46');
        $this->addSql('ALTER TABLE company DROP FOREIGN KEY FK_4FBF094F5A3C6E93');
        $this->addSql('ALTER TABLE milpac_profile DROP FOREIGN KEY FK_7B76634DF4FF7106');
        $this->addSql('ALTER TABLE milpac_profile DROP FOREIGN KEY FK_7B76634D7616678F');
        $this->addSql('ALTER TABLE milpac_profile_billet_assignment DROP FOREIGN KEY FK_D6D53288E9B537B5');
        $this->addSql('ALTER TABLE milpac_profile_billet_assignment DROP FOREIGN KEY FK_D6D53288225F40B7');
        $this->addSql('ALTER TABLE milpac_profile_uniform_override DROP FOREIGN KEY FK_8F4EC157E9B537B5');
        $this->addSql('ALTER TABLE milpac_profile_uniform_override DROP FOREIGN KEY FK_8F4EC1578447F603');
        $this->addSql('ALTER TABLE milpac_profile_uniform_override DROP FOREIGN KEY FK_8F4EC157B5EA5AE0');
        $this->addSql('ALTER TABLE platoon DROP FOREIGN KEY FK_45D91E68979B1AD6');
        $this->addSql('ALTER TABLE section DROP FOREIGN KEY FK_2D737AEFBCB0A42');
        $this->addSql('ALTER TABLE section DROP FOREIGN KEY FK_2D737AEF8447F603');
        $this->addSql('DROP TABLE section_practice');
        $this->addSql('DROP TABLE battalion');
        $this->addSql('DROP TABLE billet_assignment');
        $this->addSql('DROP TABLE billet_position');
        $this->addSql('DROP TABLE company');
        $this->addSql('DROP TABLE milpac_profile');
        $this->addSql('DROP TABLE milpac_profile_billet_assignment');
        $this->addSql('DROP TABLE milpac_profile_uniform_override');
        $this->addSql('DROP TABLE platoon');
        $this->addSql('DROP TABLE rank');
        $this->addSql('DROP TABLE section');
        $this->addSql('DROP TABLE service_branch');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
