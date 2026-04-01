<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Instrument;
use App\Models\Penalty;
use App\Models\Rental;
use App\Models\RentalDetail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class TransactionHistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ensure 'customer' role exists
        $customerRole = Role::firstOrCreate(['name' => 'customer']);

        // 2. Create some customers if they don't exist
        $customers = [];
        $customerData = [
            ['name' => 'Budi Santoso', 'email' => 'budi@example.com'],
            ['name' => 'Siti Aminah', 'email' => 'siti@example.com'],
            ['name' => 'Andi Wijaya', 'email' => 'andi@example.com'],
            ['name' => 'Dewi Lestari', 'email' => 'dewi@example.com'],
            ['name' => 'Eko Prasetyo', 'email' => 'eko@example.com'],
            ['name' => 'Fitriani', 'email' => 'fitri@example.com'],
            ['name' => 'Guntur Romli', 'email' => 'guntur@example.com'],
            ['name' => 'Hani Syabana', 'email' => 'hani@example.com'],
            ['name' => 'Irfan Hakim', 'email' => 'irfan@example.com'],
            ['name' => 'Joko Widodo', 'email' => 'joko@example.com'],
        ];

        foreach ($customerData as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'id' => Str::uuid(),
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                ]
            );
            $user->assignRole($customerRole);
            $customers[] = $user;
        }

        // 3. Ensure we have categories and instruments
        $categories = Category::all();
        if ($categories->isEmpty()) {
            $cat1 = Category::create(['id' => Str::uuid(), 'name' => 'Gitar', 'slug' => 'gitar']);
            $cat2 = Category::create(['id' => Str::uuid(), 'name' => 'Keyboard', 'slug' => 'keyboard']);
            $cat3 = Category::create(['id' => Str::uuid(), 'name' => 'Drum', 'slug' => 'drum']);
            $categories = collect([$cat1, $cat2, $cat3]);
        }

        $instruments = Instrument::all();
        if ($instruments->count() < 8) {
            $instrumentData = [
                ['name' => 'Fender Stratocaster', 'price' => 150000, 'cat' => $categories[0]],
                ['name' => 'Gibson Les Paul', 'price' => 200000, 'cat' => $categories[0]],
                ['name' => 'Yamaha PSR-E373', 'price' => 100000, 'cat' => $categories[1]],
                ['name' => 'Roland RD-2000', 'price' => 300000, 'cat' => $categories[1]],
                ['name' => 'Tama Imperialstar', 'price' => 250000, 'cat' => $categories[2]],
                ['name' => 'Pearl Export', 'price' => 220000, 'cat' => $categories[2]],
                ['name' => 'Ibanez RG Series', 'price' => 120000, 'cat' => $categories[0]],
                ['name' => 'Korg Nautilus', 'price' => 350000, 'cat' => $categories[1]],
                ['name' => 'Yamaha C40 Guitar', 'price' => 60000, 'cat' => $categories[0]],
                ['name' => 'Casio PX-S1100', 'price' => 180000, 'cat' => $categories[1]],
            ];

            foreach ($instrumentData as $data) {
                Instrument::firstOrCreate(
                    ['name' => $data['name']],
                    [
                        'id' => Str::uuid(),
                        'category_id' => $data['cat']->id,
                        'brand_id' => $data['cat']->id,
                        'name' => $data['name'],
                        'price_per_day' => $data['price'],
                        'status' => 'available',
                        'description' => 'Instrumen musik berkualitas tinggi untuk disewa.',
                    ]
                );
            }
            $instruments = Instrument::all();
        }

        // 4. Create ~50 Rental records
        $statuses = ['returned', 'ongoing', 'pending', 'approved', 'cancelled'];
        
        for ($i = 1; $i <= 50; $i++) {
            $customer = $customers[array_rand($customers)];
            $status = $statuses[array_rand($statuses)];
            
            // Random dates within the last 3 months
            $rentDate = Carbon::now()->subDays(rand(1, 90));
            $days = rand(1, 14);
            $returnDate = (clone $rentDate)->addDays($days);
            
            $actualReturnDate = null;
            if ($status === 'returned') {
                // Return date could be late or early
                $actualReturnDate = (clone $returnDate)->addDays(rand(-2, 5));
            }

            $rental = Rental::create([
                'id' => Str::uuid(),
                'customer_id' => $customer->id,
                'user_id' => null, // Admin/Staff who processed could be added if needed
                'rent_date' => $rentDate,
                'return_date' => $returnDate,
                'actual_return_date' => $actualReturnDate,
                'total_price' => 0, // Will update after details
                'status' => $status,
                'created_at' => $rentDate->subHours(rand(1, 24)),
            ]);

            // Add 1-3 instruments per rental
            $totalRentalPrice = 0;
            $numItems = rand(1, 3);
            $selectedInstruments = $instruments->random($numItems);

            foreach ($selectedInstruments as $instrument) {
                $subtotal = $instrument->price_per_day * $days;
                RentalDetail::create([
                    'id' => Str::uuid(),
                    'rental_id' => $rental->id,
                    'instrument_id' => $instrument->id,
                    'price_per_day' => $instrument->price_per_day,
                    'day' => $days,
                    'subtotal' => $subtotal,
                ]);
                $totalRentalPrice += $subtotal;
            }

            $rental->update(['total_price' => $totalRentalPrice]);

            // Add Penalty if status is returned and actual return date is late
            if ($status === 'returned' && $actualReturnDate > $returnDate) {
                $daysLate = $actualReturnDate->diffInDays($returnDate);
                if ($daysLate > 0) {
                    Penalty::create([
                        'id' => Str::uuid(),
                        'rental_id' => $rental->id,
                        'title' => 'Denda Keterlambatan',
                        'reason' => "Terlambat mengembalikan selama {$daysLate} hari.",
                        'amount' => $daysLate * 50000, // Example 50k per day
                        'created_at' => $actualReturnDate,
                    ]);
                }
            }
            
            // Randomly add damage penalty for some returned rentals
            if ($status === 'returned' && rand(1, 10) === 1) {
                Penalty::create([
                    'id' => Str::uuid(),
                    'rental_id' => $rental->id,
                    'title' => 'Denda Kerusakan',
                    'reason' => 'Terdapat goresan pada bodi instrumen.',
                    'amount' => rand(50000, 200000),
                    'created_at' => $actualReturnDate ?: $returnDate,
                ]);
            }
        }
    }
}
