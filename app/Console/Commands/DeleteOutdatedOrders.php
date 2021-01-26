<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Repositories\Orders\DepositRepository;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DeleteOutdatedOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:clean {days=62}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '刪除過期訂單';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(DepositRepository $depositRepo)
    {
        $days = $this->argument('days');
        $date = Carbon::now()->subDays($days);
        $depositRepo->before($date)->delete();
    }
}
