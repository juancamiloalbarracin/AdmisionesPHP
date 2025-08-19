<?php
namespace UDC\SistemaAdmisiones\Models;

use UDC\SistemaAdmisiones\Utils\Database;
use Exception;

class InfoAcademicaClean
{
    private Database $db;
    private string $table = 'info_academica';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getByUserId(int $userId): ?array
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE user_id = ? LIMIT 1";
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$userId]);
            $r = $stmt->fetch();
            return $r ?: null;
        } catch (Exception $e) {
            error_log('[INFO_ACADEMICA_CLEAN] getByUserId error: ' . $e->getMessage());
            return null;
        }
    }

    public function saveForUser(int $userId, array $data): bool
    {
        try {
            // Check if exists
            $existing = $this->getByUserId($userId);
            if ($existing) {
                // build update
                $sets = [];
                $params = [];
                foreach ($data as $k=>$v) { $sets[] = "$k = :$k"; $params[":$k"] = $v; }
                $params[':user_id'] = $userId;
                $sql = "UPDATE {$this->table} SET " . implode(', ', $sets) . " WHERE user_id = :user_id";
                $stmt = $this->db->getConnection()->prepare($sql);
                return $stmt->execute($params);
            } else {
                // insert
                $cols = array_keys($data);
                $placeholders = array_map(function($c){return ':' . $c;}, $cols);
                $sql = "INSERT INTO {$this->table} (user_id, " . implode(',', $cols) . ", created_at, updated_at) VALUES (:user_id, " . implode(',', $placeholders) . ", NOW(), NOW())";
                $params = [];
                foreach ($data as $k=>$v) $params[":$k"] = $v;
                $params[':user_id'] = $userId;
                $stmt = $this->db->getConnection()->prepare($sql);
                return $stmt->execute($params);
            }
        } catch (Exception $e) {
            error_log('[INFO_ACADEMICA_CLEAN] saveForUser error: ' . $e->getMessage());
            return false;
        }
    }
}
