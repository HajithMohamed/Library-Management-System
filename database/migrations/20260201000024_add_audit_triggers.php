<?php

use Phinx\Migration\AbstractMigration;

class AddAuditTriggers extends AbstractMigration
{
    public function up()
    {
        // Phinx doesn't have built-in trigger methods, so we use execute()

        // Trigger for users table
        $this->execute("
            CREATE TRIGGER trg_users_audit_update AFTER UPDATE ON users
            FOR EACH ROW
            BEGIN
                IF (OLD.deleted_at IS NULL AND NEW.deleted_at IS NOT NULL) THEN
                    INSERT INTO audit_logs (userId, action, entityType, entityId, changes, createdAt)
                    VALUES (NEW.userId, 'SOFT_DELETE', 'user', NEW.userId, JSON_OBJECT('deleted_at', NEW.deleted_at), NOW());
                ELSE
                    INSERT INTO audit_logs (userId, action, entityType, entityId, changes, createdAt)
                    VALUES (NEW.userId, 'UPDATE', 'user', NEW.userId, JSON_OBJECT('old', JSON_OBJECT('username', OLD.username, 'email', OLD.emailId), 'new', JSON_OBJECT('username', NEW.username, 'email', NEW.emailId)), NOW());
                END IF;
            END
        ");

        // Trigger for books table
        $this->execute("
            CREATE TRIGGER trg_books_audit_update AFTER UPDATE ON books
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_logs (userId, action, entityType, entityId, changes, createdAt)
                VALUES ('system', 'UPDATE', 'book', NEW.isbn, JSON_OBJECT('bookName', NEW.bookName, 'available', NEW.available), NOW());
            END
        ");
    }

    public function down()
    {
        $this->execute("DROP TRIGGER IF EXISTS trg_users_audit_update");
        $this->execute("DROP TRIGGER IF EXISTS trg_books_audit_update");
    }
}
