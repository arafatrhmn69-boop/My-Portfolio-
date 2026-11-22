<?php

declare(strict_types=1);

/**
 * Trips / destinations model.
 *
 * Every query uses PDO prepared statements with bound parameters. The only
 * dynamically-built SQL fragment is the WHERE clause shape, which is fixed
 * by the code — user input is always bound, never interpolated.
 */
final class Trip
{
    public function __construct(private PDO $pdo)
    {
    }

    public function count(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM trips')->fetchColumn();
    }

    public function countriesCount(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(DISTINCT country) FROM trips')->fetchColumn();
    }

    /**
     * @return array{items: array<int, array<string, mixed>>, total: int, page: int, pages: int, per_page: int}
     */
    public function paginate(int $page, int $perPage = TRIPS_PER_PAGE, string $search = ''): array
    {
        $search = trim($search);
        $where = '';
        $params = [];

        if ($search !== '') {
            $where = " WHERE (destination_name LIKE :search ESCAPE '\\'
                        OR country LIKE :search ESCAPE '\\'
                        OR description LIKE :search ESCAPE '\\')";
            $params[':search'] = self::likePattern($search);
        }

        $countStatement = $this->pdo->prepare('SELECT COUNT(*) FROM trips' . $where);
        $countStatement->execute($params);
        $total = (int) $countStatement->fetchColumn();

        $perPage = max(1, $perPage);
        $pages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $pages));
        $offset = ($page - 1) * $perPage;

        $statement = $this->pdo->prepare(
            'SELECT * FROM trips' . $where . ' ORDER BY travel_date DESC, id DESC LIMIT :limit OFFSET :offset'
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
    public function all(int $limit = 100): array
    {
        $limit = max(1, min($limit, 500));
        $statement = $this->pdo->prepare(
            'SELECT * FROM trips ORDER BY travel_date DESC, id DESC LIMIT :limit'
        );
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function recent(int $limit = 4): array
    {
        return $this->all($limit);
    }

    public function find(int $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM trips WHERE id = :id LIMIT 1');
        $statement->execute([':id' => $id]);
        $trip = $statement->fetch();

        return is_array($trip) ? $trip : null;
    }

    /**
     * @param array<string, mixed> $data Must come from Trip::validate().
     */
    public function create(array $data, ?string $imagePath = null): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO trips
                (destination_name, country, description, price_or_budget, travel_date, image_path)
             VALUES
                (:destination_name, :country, :description, :price_or_budget, :travel_date, :image_path)'
        );

        $statement->execute([
            ':destination_name' => $data['destination_name'],
            ':country'          => $data['country'],
            ':description'      => $data['description'],
            ':price_or_budget'  => $data['price_or_budget'],
            ':travel_date'      => $data['travel_date'],
            ':image_path'       => $imagePath,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * @param array<string, mixed> $data Must come from Trip::validate().
     */
    public function update(int $id, array $data, ?string $imagePath): bool
    {
        $statement = $this->pdo->prepare(
            'UPDATE trips SET
                destination_name = :destination_name,
                country = :country,
                description = :description,
                price_or_budget = :price_or_budget,
                travel_date = :travel_date,
                image_path = :image_path
             WHERE id = :id'
        );

        return $statement->execute([
            ':destination_name' => $data['destination_name'],
            ':country'          => $data['country'],
            ':description'      => $data['description'],
            ':price_or_budget'  => $data['price_or_budget'],
            ':travel_date'      => $data['travel_date'],
            ':image_path'       => $imagePath,
            ':id'               => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $statement = $this->pdo->prepare('DELETE FROM trips WHERE id = :id');

        return $statement->execute([':id' => $id]);
    }

    /**
     * Normalise and validate form input.
     *
     * @param array<string, mixed> $input
     * @return array{0: array<string, string>, 1: array<string, string>} [data, errors]
     */
    public static function validate(array $input): array
    {
        $data = [
            'destination_name' => input_string($input, 'destination_name'),
            'country'          => input_string($input, 'country'),
            'description'      => input_string($input, 'description'),
            'price_or_budget'  => input_string($input, 'price_or_budget'),
            'travel_date'      => input_string($input, 'travel_date'),
        ];

        $validator = (new Validator($data))
            ->required('destination_name', 'Destination name')
            ->maxLength('destination_name', 120, 'Destination name')
            ->required('country', 'Country')
            ->maxLength('country', 80, 'Country')
            ->required('description', 'Description')
            ->maxLength('description', 5000, 'Description')
            ->maxLength('price_or_budget', 60, 'Price or budget')
            ->required('travel_date', 'Travel date')
            ->date('travel_date', 'Travel date');

        return [$data, $validator->errors()];
    }

    private static function likePattern(string $search): string
    {
        $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $search);

        return '%' . $escaped . '%';
    }
}
