<?php

declare(strict_types=1);

/**
 * Gallery photos model.
 *
 * A photo belongs to a free-text album name (for example "Cox's Bazar"
 * or "Coastal Light"). Every query is a PDO prepared statement.
 */
final class GalleryPhoto
{
    public function __construct(private PDO $pdo)
    {
    }

    public function count(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM gallery_photos')->fetchColumn();
    }

    /**
     * Albums with a photo count and a cover image.
     *
     * @return array<int, array{album_name: string, total: int, cover: ?string}>
     */
    public function albums(): array
    {
        $statement = $this->pdo->query(
            "SELECT g1.album_name,
                    COUNT(*) AS total,
                    (
                        SELECT g2.image_path
                        FROM gallery_photos g2
                        WHERE g2.album_name = g1.album_name
                        ORDER BY g2.created_at DESC, g2.id DESC
                        LIMIT 1
                    ) AS cover
             FROM gallery_photos g1
             GROUP BY g1.album_name
             ORDER BY g1.album_name COLLATE NOCASE ASC"
        );

        return array_map(static function (array $album): array {
            return [
                'album_name' => (string) $album['album_name'],
                'total'      => (int) $album['total'],
                'cover'      => $album['cover'] !== null ? (string) $album['cover'] : null,
            ];
        }, $statement->fetchAll());
    }

    /**
     * @return array{items: array<int, array<string, mixed>>, total: int, page: int, pages: int, per_page: int}
     */
    public function paginate(int $page, int $perPage = GALLERY_PER_PAGE, string $album = '', string $search = ''): array
    {
        [$where, $params] = $this->buildWhere($album, $search);

        $countStatement = $this->pdo->prepare('SELECT COUNT(*) FROM gallery_photos' . $where);
        $countStatement->execute($params);
        $total = (int) $countStatement->fetchColumn();

        $perPage = max(1, $perPage);
        $pages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $pages));
        $offset = ($page - 1) * $perPage;

        $statement = $this->pdo->prepare(
            'SELECT * FROM gallery_photos' . $where . ' ORDER BY created_at DESC, id DESC LIMIT :limit OFFSET :offset'
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
     * Public listing, optionally limited to one album.
     *
     * @return array<int, array<string, mixed>>
     */
    public function all(string $album = '', int $limit = 500): array
    {
        $limit = max(1, min($limit, 1000));
        $sql = 'SELECT * FROM gallery_photos';
        $params = [];

        if ($album !== '') {
            $sql .= ' WHERE album_name = :album COLLATE NOCASE';
            $params[':album'] = $album;
        }

        $sql .= ' ORDER BY created_at DESC, id DESC LIMIT :limit';

        $statement = $this->pdo->prepare($sql);

        foreach ($params as $key => $value) {
            $statement->bindValue($key, $value, PDO::PARAM_STR);
        }

        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM gallery_photos WHERE id = :id LIMIT 1');
        $statement->execute([':id' => $id]);
        $photo = $statement->fetch();

        return is_array($photo) ? $photo : null;
    }

    public function create(string $albumName, string $caption, string $imagePath): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO gallery_photos (album_name, caption, image_path)
             VALUES (:album_name, :caption, :image_path)'
        );
        $statement->execute([
            ':album_name' => $albumName,
            ':caption'    => $caption,
            ':image_path' => $imagePath,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function delete(int $id): bool
    {
        $statement = $this->pdo->prepare('DELETE FROM gallery_photos WHERE id = :id');

        return $statement->execute([':id' => $id]);
    }

    /**
     * Trim, collapse whitespace and cap the album name length.
     */
    public static function normaliseAlbum(string $album): string
    {
        $album = preg_replace('/\s+/u', ' ', trim($album)) ?? '';
        $album = mb_substr($album, 0, GALLERY_MAX_ALBUM_NAME, 'UTF-8');

        return trim($album);
    }

    /**
     * @return array{0: string, 1: array<string, string>}
     */
    private function buildWhere(string $album, string $search): array
    {
        $clauses = [];
        $params = [];

        if ($album !== '') {
            $clauses[] = 'album_name = :album COLLATE NOCASE';
            $params[':album'] = $album;
        }

        if ($search !== '') {
            $clauses[] = "caption LIKE :search ESCAPE '\\'";
            $params[':search'] = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $search) . '%';
        }

        return [$clauses === [] ? '' : ' WHERE ' . implode(' AND ', $clauses), $params];
    }
}
