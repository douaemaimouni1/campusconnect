$notifs = \Illuminate\Notifications\DatabaseNotification::latest()->take(5)->get(['id','type','notifiable_id','data','created_at']);
foreach ($notifs as $n) {
    echo "----\n";
    echo "type: " . $n->type . "\n";
    echo "notifiable_id: " . $n->notifiable_id . "\n";
    echo "data: " . json_encode($n->data) . "\n";
    echo "created_at: " . $n->created_at . "\n";
}
