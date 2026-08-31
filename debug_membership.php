$u = App\Models\User::where("email","maimounidouae2005@gmail.com")->first();
$c = App\Models\Club::where("name","test club")->first();
echo "user_id: " . $u->id . "\n";
echo "club_id: " . $c->id . "\n";
$memberships = App\Models\ClubMembership::where("user_id", $u->id)->where("club_id", $c->id)->get(["id","status","requested_at","responded_at"]);
foreach ($memberships as $m) {
    print_r($m->toArray());
}
