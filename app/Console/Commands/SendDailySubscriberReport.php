<?php

namespace App\Console\Commands;

use App\Mail\DailySubscriberReport;
use App\Models\NewsletterSubscriber;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendDailySubscriberReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-daily-subscriber-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily report of new subscribers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $yesterday = now()->subDay();
        $newSubscribers = NewsletterSubscriber::where('created_at', '>=', $yesterday)->get();
        
        $this->info('Nombre de nouveaux abonnés trouvés: ' . $newSubscribers->count()); 

        if ($newSubscribers->count() > 0) {

            try {
                Mail::to('sihiotsinfo@gmail.com')
                ->cc('direction@softskills.ci')
                ->send(new DailySubscriberReport($newSubscribers));

                $this->info('Email envoyé avec succès.');

            } catch (\Exception $e) {

                $this->error('Échec de l\'envoi de l\'email : ' . $e->getMessage());
            }
        }
    }
}
