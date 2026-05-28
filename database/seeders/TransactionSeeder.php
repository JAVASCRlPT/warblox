<?php

namespace Database\Seeders;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Book;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get mahasiswa users (skip admin)
        $mahasiswa = User::where('role', 'mahasiswa')->get();
        $books = Book::all();

        // Create sample transactions
        $transactions = [
            // Currently borrowed
            [
                'user_id' => $mahasiswa[0]->id,
                'book_id' => $books[0]->id,
                'borrow_date' => Carbon::now()->subDays(2),
                'due_date' => Carbon::now()->addDays(5),
                'return_date' => null,
                'status' => 'dipinjam',
                'fine' => 0,
            ],
            [
                'user_id' => $mahasiswa[1]->id,
                'book_id' => $books[1]->id,
                'borrow_date' => Carbon::now()->subDays(3),
                'due_date' => Carbon::now()->addDays(4),
                'return_date' => null,
                'status' => 'dipinjam',
                'fine' => 0,
            ],
            // Overdue
            [
                'user_id' => $mahasiswa[2]->id,
                'book_id' => $books[5]->id,
                'borrow_date' => Carbon::now()->subDays(15),
                'due_date' => Carbon::now()->subDays(8),
                'return_date' => null,
                'status' => 'terlambat',
                'fine' => 35000, // 7 hari x 5000
            ],
            // Returned on time
            [
                'user_id' => $mahasiswa[0]->id,
                'book_id' => $books[2]->id,
                'borrow_date' => Carbon::now()->subDays(14),
                'due_date' => Carbon::now()->subDays(7),
                'return_date' => Carbon::now()->subDays(7),
                'status' => 'kembali',
                'fine' => 0,
            ],
            // Returned late
            [
                'user_id' => $mahasiswa[3]->id,
                'book_id' => $books[3]->id,
                'borrow_date' => Carbon::now()->subDays(20),
                'due_date' => Carbon::now()->subDays(13),
                'return_date' => Carbon::now()->subDays(11),
                'status' => 'terlambat',
                'fine' => 10000, // 2 hari x 5000
            ],
            // Another returned on time
            [
                'user_id' => $mahasiswa[4]->id,
                'book_id' => $books[7]->id,
                'borrow_date' => Carbon::now()->subDays(10),
                'due_date' => Carbon::now()->subDays(3),
                'return_date' => Carbon::now()->subDays(3),
                'status' => 'kembali',
                'fine' => 0,
            ],
        ];

        foreach ($transactions as $transaction) {
            Transaction::create($transaction);
        }
    }
}
