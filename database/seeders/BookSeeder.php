<?php

namespace Database\Seeders;

use App\Models\Book;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $books = [
            // Teknologi & Pemrograman (Category 1)
            [
                'title' => 'Clean Code: A Handbook of Agile Software Craftsmanship',
                'author' => 'Robert C. Martin',
                'publisher' => 'Prentice Hall',
                'year' => 2008,
                'stock' => 5,
                'category_id' => 1,
                'description' => 'Panduan lengkap untuk menulis kode yang bersih dan profesional',
            ],
            [
                'title' => 'Design Patterns: Elements of Reusable Object-Oriented Software',
                'author' => 'Gang of Four',
                'publisher' => 'Addison-Wesley',
                'year' => 1994,
                'stock' => 3,
                'category_id' => 1,
                'description' => 'Buku klasik tentang design pattern dalam OOP',
            ],
            [
                'title' => 'The Pragmatic Programmer',
                'author' => 'David Thomas',
                'publisher' => 'Addison-Wesley',
                'year' => 1999,
                'stock' => 4,
                'category_id' => 1,
                'description' => 'Tips dan trik untuk programmer profesional',
            ],
            [
                'title' => 'Introduction to Algorithms',
                'author' => 'Thomas H. Cormen',
                'publisher' => 'MIT Press',
                'year' => 2009,
                'stock' => 2,
                'category_id' => 1,
                'description' => 'Buku teks komprehensif tentang algoritma dan struktur data',
            ],
            [
                'title' => 'Refactoring: Improving the Design of Existing Code',
                'author' => 'Martin Fowler',
                'publisher' => 'Addison-Wesley',
                'year' => 1999,
                'stock' => 3,
                'category_id' => 1,
                'description' => 'Teknik refactoring untuk meningkatkan kualitas kode',
            ],

            // Bisnis & Entrepreneur (Category 2)
            [
                'title' => 'The Lean Startup',
                'author' => 'Eric Ries',
                'publisher' => 'Crown Business',
                'year' => 2011,
                'stock' => 6,
                'category_id' => 2,
                'description' => 'Metodologi startup modern untuk membangun bisnis yang sukses',
            ],
            [
                'title' => 'Good to Great: Why Some Companies Make the Leap',
                'author' => 'Jim Collins',
                'publisher' => 'HarperBusiness',
                'year' => 2001,
                'stock' => 4,
                'category_id' => 2,
                'description' => 'Analisis perusahaan dari biasa menjadi luar biasa',
            ],
            [
                'title' => 'The Art of War',
                'author' => 'Sun Tzu',
                'publisher' => 'Penguin Classics',
                'year' => 2006,
                'stock' => 7,
                'category_id' => 2,
                'description' => 'Strategi kuno yang diterapkan dalam bisnis modern',
            ],

            // Sains & Pengetahuan Alam (Category 3)
            [
                'title' => 'A Brief History of Time',
                'author' => 'Stephen Hawking',
                'publisher' => 'Bantam',
                'year' => 1988,
                'stock' => 5,
                'category_id' => 3,
                'description' => 'Penjelasan tentang waktu, ruang, dan alam semesta',
            ],
            [
                'title' => 'The Selfish Gene',
                'author' => 'Richard Dawkins',
                'publisher' => 'Oxford University Press',
                'year' => 1976,
                'stock' => 3,
                'category_id' => 3,
                'description' => 'Perspektif revolusioner tentang evolusi dan genetika',
            ],

            // Sastra & Novel (Category 4)
            [
                'title' => '1984',
                'author' => 'George Orwell',
                'publisher' => 'Secker & Warburg',
                'year' => 1949,
                'stock' => 8,
                'category_id' => 4,
                'description' => 'Novel distopia tentang totalitarianisme',
            ],
            [
                'title' => 'Pride and Prejudice',
                'author' => 'Jane Austen',
                'publisher' => 'T. Egerton',
                'year' => 1813,
                'stock' => 6,
                'category_id' => 4,
                'description' => 'Novel klasik tentang cinta dan masyarakat',
            ],
            [
                'title' => 'The Great Gatsby',
                'author' => 'F. Scott Fitzgerald',
                'publisher' => 'Scribner',
                'year' => 1925,
                'stock' => 5,
                'category_id' => 4,
                'description' => 'Masterpiece tentang American Dream',
            ],

            // Sejarah & Budaya (Category 5)
            [
                'title' => 'Sapiens: A Brief History of Humankind',
                'author' => 'Yuval Noah Harari',
                'publisher' => 'Harvill Secker',
                'year' => 2011,
                'stock' => 7,
                'category_id' => 5,
                'description' => 'Sejarah umat manusia dari masa prasejarah hingga modern',
            ],
            [
                'title' => 'The Story of Civilization',
                'author' => 'Will Durant',
                'publisher' => 'Simon and Schuster',
                'year' => 1935,
                'stock' => 2,
                'category_id' => 5,
                'description' => 'Survei komprehensif tentang peradaban manusia',
            ],

            // Motivasi & Self-Development (Category 6)
            [
                'title' => 'Atomic Habits',
                'author' => 'James Clear',
                'publisher' => 'Avery',
                'year' => 2018,
                'stock' => 10,
                'category_id' => 6,
                'description' => 'Cara membangun kebiasaan baik dan mengubah hidup',
            ],
            [
                'title' => 'The 7 Habits of Highly Effective People',
                'author' => 'Stephen Covey',
                'publisher' => 'Free Press',
                'year' => 1989,
                'stock' => 6,
                'category_id' => 6,
                'description' => '7 kebiasaan untuk menjadi orang yang efektif',
            ],
            [
                'title' => 'Mindset: The New Psychology of Success',
                'author' => 'Carol S. Dweck',
                'publisher' => 'Random House',
                'year' => 2006,
                'stock' => 5,
                'category_id' => 6,
                'description' => 'Kekuatan mindset dalam mencapai kesuksesan',
            ],

            // Pendidikan (Category 7)
            [
                'title' => 'Learning How to Learn',
                'author' => 'Barbara Oakley',
                'publisher' => 'TarcherPerigee',
                'year' => 2014,
                'stock' => 4,
                'category_id' => 7,
                'description' => 'Teknik efektif untuk belajar dengan lebih baik',
            ],

            // Seni & Desain (Category 8)
            [
                'title' => 'The Design of Everyday Things',
                'author' => 'Don Norman',
                'publisher' => 'Basic Books',
                'year' => 2002,
                'stock' => 5,
                'category_id' => 8,
                'description' => 'Filosofi desain yang user-friendly',
            ],
        ];

        foreach ($books as $book) {
            Book::create($book);
        }
    }
}
