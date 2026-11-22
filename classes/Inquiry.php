<?php

declare(strict_types=1);

/**
 * Contact-form inquiries model. All SQL uses bound parameters.
 */
final class Inquiry
{
    public function __construct(private PDO $pdo)
    {
    }

    public function count(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM inquiries')->fetchColumn();
    }

    public function countSince(string $utcDateTime): int
    {
        $statement = $this->pdo->prepare('SELECT COUNT(*) FROM inquiries WHERE created_at >= :since');
        $statement->execute([':since' => $utcDateTime]);

        return (int) $statement->fetchColumn();
    }

    /**
     * @return array{items: array<int, array<string, mixed>>, total: int, page: int, pages: int, per_page: int}
     */
    public function paginate(int $page, int $perPage = INQUIRIES_PER_PAGE, string $search = ''): array
    {
        $search = trim($search);
        $where = '';
        $params = [];

        if ($search !== '') {
            $where = " WHERE (sender_name LIKE :search ESCAPE '\\'
                        OR email LIKE :search ESCAPE '\\'
                        OR message LIKE :search ESCAPE '\\')";
            $params[':search'] = self::likePattern($search);
        }

        $countStatement = $this->pdo->prepare('SELECT COUNT(*) FROM inquiries' . $where);
        $countStatement->execute($params);
        $total = (int) $countStatement->fetchColumn();

        $perPage = max(1, $perPage);
        $pages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $pages));
        $offset = ($page - 1) * $perPage;

        $statement = $this->pdo->prepare(
            'SELECT * FROM inquiries' . $where . ' ORDER BY created_at DESC, id DESC LIMIT :limit OFFSET :offset'
        );

        foreach ($params as $key => $value) {
            $statement->bindValue($key, $value, PDO::PARAM_STR);
        }

        $statement->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $statement->execute();

        return [
            'items'    => $statement->fetchAll(),
            'total'    => $total,
            'page'     => $page,
            'pages'    => $pages,
            'per_page' => $perPage,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function recent(int $limit = 5): array
    {
        $limit = max(1, min($limit, 100));
        $statement = $this->pdo->prepare(
            'SELECT * FROM inquiries ORDER BY created_at DESC, id DESC LIMIT :limit'
        );
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM inquiries WHERE id = :id LIMIT 1');
        $statement->execute([':id' => $id]);
        $inquiry = $statement->fetch();

        return is_array($inquiry) ? $inquiry : null;
    }

    /**
     * @param array{sender_name: string, email: string, message: string} $data
     */
    public function create(array $data): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO inquiries (sender_name, email, message)
             VALUES (:sender_name, :email, :message)'
        );

        $statement->execute([
            ':sender_name' => $data['sender_name'],
            ':email'       => $data['email'],
            ':message'     => $data['message'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function delete(int $id): bool
    {
        $statement = $this->pdo->prepare('DELETE FROM inquiries WHERE id = :id');

        return $statement->execute([':id' => $id]);
    }

    private static function likePattern(string $search): string
    {
        $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $search);

        return '%' . $escaped . '%';
    }
}
