<?php
// ── POST /api/submit_reservation.php ──────────────────
// Accepts a reservation form submission, saves to DB,
// and sends notification email to the hotel.
// ──────────────────────────────────────────────────────
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

require_once __DIR__ . '/../config/database.php';

// ── Parse input (JSON or form-encoded) ────────────────
$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?? $_POST;

// ── Validate required fields ───────────────────────────
$errors = [];
$prenom = sanitize($data['prenom'] ?? '');
$nom    = sanitize($data['nom']    ?? '');
$email  = filter_var(trim($data['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$type   = in_array($data['type'] ?? '', ['chambre','table','event']) ? $data['type'] : 'chambre';

if (!$prenom) $errors[] = 'Le prénom est requis.';
if (!$nom)    $errors[] = 'Le nom est requis.';
if (!$email)  $errors[] = 'Un email valide est requis.';

if ($errors) {
    http_response_code(422);
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

// ── Collect optional fields ────────────────────────────
$telephone   = sanitize($data['telephone']   ?? '');
$message     = sanitize($data['message']     ?? '');
$checkin     = $data['checkin']     ?? null;
$checkout    = $data['checkout']    ?? null;
$room_type   = sanitize($data['room_type']   ?? '');
$guests      = sanitize($data['guests']      ?? '');
$rest_date   = $data['rest_date']   ?? null;
$rest_time   = sanitize($data['rest_time']   ?? '');
$covers      = sanitize($data['covers']      ?? '');
$event_type  = sanitize($data['event_type']  ?? '');
$event_date  = $data['event_date']  ?? null;
$participants = intval($data['participants'] ?? 0);

// ── Insert into DB ─────────────────────────────────────
try {
    $db = getDB();
    $stmt = $db->prepare("
        INSERT INTO reservations
          (type, prenom, nom, email, telephone,
           checkin, checkout, room_type, guests,
           rest_date, rest_time, covers,
           event_type, event_date, participants,
           message, status)
        VALUES
          (:type, :prenom, :nom, :email, :telephone,
           :checkin, :checkout, :room_type, :guests,
           :rest_date, :rest_time, :covers,
           :event_type, :event_date, :participants,
           :message, 'nouveau')
    ");
    $stmt->execute([
        ':type'         => $type,
        ':prenom'       => $prenom,
        ':nom'          => $nom,
        ':email'        => $email,
        ':telephone'    => $telephone,
        ':checkin'      => $checkin  ?: null,
        ':checkout'     => $checkout ?: null,
        ':room_type'    => $room_type,
        ':guests'       => $guests,
        ':rest_date'    => $rest_date  ?: null,
        ':rest_time'    => $rest_time,
        ':covers'       => $covers,
        ':event_type'   => $event_type,
        ':event_date'   => $event_date ?: null,
        ':participants' => $participants ?: null,
        ':message'      => $message,
    ]);
    $reservationId = $db->lastInsertId();

    // ── Send notification email to hotel ───────────────
    $hotelEmail   = 'hotelbaribaplaya.28@gmail.com';
    $hotelName    = 'Hôtel Bariba Playa';
    $subject      = "[Réservation #$reservationId] Nouvelle demande — $prenom $nom";

    $typeLabels = ['chambre' => 'Chambre', 'table' => 'Table Restaurant', 'event' => 'Événement'];
    $typeLabel  = $typeLabels[$type] ?? $type;

    $body  = "Nouvelle demande de réservation reçue via le site web.\n\n";
    $body .= "═══════════════════════════════\n";
    $body .= "Référence : #$reservationId\n";
    $body .= "Type       : $typeLabel\n";
    $body .= "Date       : " . date('d/m/Y H:i') . "\n";
    $body .= "═══════════════════════════════\n\n";
    $body .= "CLIENT\n";
    $body .= "Nom    : $prenom $nom\n";
    $body .= "Email  : $email\n";
    $body .= "Tél    : $telephone\n\n";

    if ($type === 'chambre') {
        $body .= "CHAMBRE\n";
        $body .= "Arrivée  : $checkin\n";
        $body .= "Départ   : $checkout\n";
        $body .= "Chambre  : $room_type\n";
        $body .= "Voyageurs: $guests\n\n";
    } elseif ($type === 'table') {
        $body .= "RESTAURANT\n";
        $body .= "Date     : $rest_date\n";
        $body .= "Heure    : $rest_time\n";
        $body .= "Couverts : $covers\n\n";
    } else {
        $body .= "ÉVÉNEMENT\n";
        $body .= "Type          : $event_type\n";
        $body .= "Date          : $event_date\n";
        $body .= "Participants  : $participants\n\n";
    }

    if ($message) {
        $body .= "MESSAGE DU CLIENT\n$message\n\n";
    }

    $body .= "═══════════════════════════════\n";
    $body .= "Gérer cette réservation dans le panel admin :\n";
    $body .= "http://votredomaine.com/admin/reservations.php\n";

    $headers  = "From: $hotelName <noreply@bariba-playa.com>\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    @mail($hotelEmail, $subject, $body, $headers);

    // ── Send confirmation email to client ──────────────
    $clientSubject = "✅ Votre demande au $hotelName — Réf. #$reservationId";
    $clientBody  = "Bonjour $prenom,\n\n";
    $clientBody .= "Nous avons bien reçu votre demande de réservation (#$reservationId).\n";
    $clientBody .= "Notre équipe vous contactera dans les plus brefs délais, généralement sous 2 heures.\n\n";
    $clientBody .= "En attendant, vous pouvez nous joindre directement :\n";
    $clientBody .= "📞 +229 97 85 65 00\n";
    $clientBody .= "💬 WhatsApp : +229 97 85 65 00\n\n";
    $clientBody .= "Cordialement,\nL'équipe $hotelName\n";
    $clientBody .= "Fidjrossè, Cotonou, Bénin\n";

    $clientHeaders  = "From: $hotelName <noreply@bariba-playa.com>\r\n";
    $clientHeaders .= "Content-Type: text/plain; charset=UTF-8\r\n";
    @mail($email, $clientSubject, $clientBody, $clientHeaders);

    echo json_encode([
        'success' => true,
        'id'      => $reservationId,
        'message' => 'Demande enregistrée. Vous allez recevoir un email de confirmation.',
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erreur serveur. Veuillez réessayer.']);
    // Log error (don't expose to client)
    error_log('[Bariba Playa] DB Error: ' . $e->getMessage());
}
