<?php

use Phinx\Seed\AbstractSeed;

class SampleBooksSeeder extends AbstractSeed
{
    public function run(): void
    {
        $categories = ['Science', 'Technology', 'Fiction', 'Literature', 'History', 'Self-help', 'Fantasy', 'Biography'];
        $publishers = ['O\'Reilly', 'Pearson', 'Springer', 'HarperCollins', 'Penguin', 'Macmillan'];
        $authors = ['Robert C. Martin', 'Martin Fowler', 'J.K. Rowling', 'Stephen King', 'Isaac Asimov', 'Yuval Noah Harari', 'James Clear', 'Carol Dweck'];

        $data = [];
        for ($i = 1; $i <= 55; $i++) {
            $isbn = str_pad($i, 13, '9780000000000', STR_PAD_LEFT);
            $cat = $categories[array_rand($categories)];
            $total = rand(5, 20);
            $borrowed = rand(0, $total);

            $data[] = [
                'isbn' => $isbn,
                'bookName' => "Sample Book Title " . $i,
                'authorName' => $authors[array_rand($authors)],
                'publisherName' => $publishers[array_rand($publishers)],
                'description' => "This is a detailed description for sample book " . $i . ". It contains info about " . $cat . " and other topics.",
                'category' => $cat,
                'publicationYear' => rand(1990, 2024),
                'totalCopies' => $total,
                'available' => $total - $borrowed,
                'borrowed' => $borrowed,
                'isTrending' => (rand(1, 100) > 80) ? 1 : 0,
                'isSpecial' => (rand(1, 100) > 90) ? 1 : 0,
                'specialBadge' => (rand(1, 100) > 90) ? 'New Arrival' : NULL,
                'createdAt' => date('Y-m-d H:i:s'),
                'updatedAt' => date('Y-m-d H:i:s'),
            ];
        }

        $this->table('books')->insert($data)->saveData();
    }
}
