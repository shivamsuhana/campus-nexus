<?php
$pageTitle = 'Attendance';
$pageScripts = ['attendance.js'];
require_once __DIR__ . '/../includes/header.php';
requireLogin();
$db = getDB();

// Faculty: create session
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isFacultyOrAdmin() && isset($_POST['create_session'])) {
    $code = generateSessionCode();
    $subject = sanitize($_POST['subject']);
    $duration = intval($_POST['duration'] ?? 15);
    $endTime = date('Y-m-d H:i:s', strtotime("+{$duration} minutes"));
    $stmt = $db->prepare("INSERT INTO attendance_sessions (faculty_id, subject, session_code, end_time) VALUES (?,?,?,?)");
    $stmt->execute([getCurrentUserId(), $subject, $code, $endTime]);
    setFlashMessage('success', "Session created! Code: $code");
    header('Location: attendance.php'); exit;
}

// Student: mark attendance
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isStudent() && isset($_POST['mark_attendance'])) {
    $code = sanitize($_POST['session_code']);
    $session = $db->prepare("SELECT * FROM attendance_sessions WHERE session_code=? AND is_active=1 AND end_time > NOW()");
    $session->execute([$code]);
    $session = $session->fetch();
    if (!$session) { setFlashMessage('error', 'Invalid or expired session code.'); }
    else {
        $exists = $db->prepare("SELECT id FROM attendance_records WHERE session_id=? AND student_id=?");
        $exists->execute([$session['id'], getCurrentUserId()]);
        if ($exists->fetch()) { setFlashMessage('warning', 'Already marked for this session.'); }
        else {
            $db->prepare("INSERT INTO attendance_records (session_id, student_id) VALUES (?,?)")->execute([$session['id'], getCurrentUserId()]);
            setFlashMessage('success', 'Attendance marked for ' . $session['subject'] . '!');
        }
    }
    header('Location: attendance.php'); exit;
}

// Active sessions (faculty)
$activeSessions = [];
if (isFacultyOrAdmin()) {
    $stmt = $db->prepare("SELECT * FROM attendance_sessions WHERE faculty_id=? AND is_active=1 ORDER BY start_time DESC");
    $stmt->execute([getCurrentUserId()]);
    $activeSessions = $stmt->fetchAll();
}

// Attendance history
if (isStudent()) {
    $history = $db->prepare("SELECT ar.marked_at, s.subject, s.session_code, u.name as faculty_name FROM attendance_records ar JOIN attendance_sessions s ON ar.session_id=s.id JOIN users u ON s.faculty_id=u.id WHERE ar.student_id=? ORDER BY ar.marked_at DESC LIMIT 20");
    $history->execute([getCurrentUserId()]);
    $history = $history->fetchAll();
} else {
    $history = $db->prepare("SELECT s.*, COUNT(ar.id) as student_count FROM attendance_sessions s LEFT JOIN attendance_records ar ON s.id=ar.session_id WHERE s.faculty_id=? GROUP BY s.id ORDER BY s.start_time DESC LIMIT 20");
    $history->execute([getCurrentUserId()]);
    $history = $history->fetchAll();
}
?>
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="page-content with-sidebar">
    <div class="module-page-header">
        <div class="module-icon" style="background:rgba(79,172,254,0.12);color:var(--clr-attendance);"><i class="fas fa-clipboard-check"></i></div>
        <div><h1>Smart Attendance</h1><p><?= isFacultyOrAdmin() ? 'Generate codes & track attendance' : 'Mark attendance with session codes' ?></p></div>
    </div>

    <?php if (isFacultyOrAdmin()): ?>
    <!-- Faculty: Create Session -->
    <div class="card" style="max-width:600px;margin-bottom:var(--space-xl);">
        <h3 style="margin-bottom:var(--space-lg);">Create Attendance Session</h3>
        <form method="POST">
            <input type="hidden" name="create_session" value="1">
            <div class="form-row">
                <div class="form-group"><label class="form-label">Subject *</label><input type="text" name="subject" class="form-control" placeholder="e.g., Data Structures" required></div>
                <div class="form-group"><label class="form-label">Duration (minutes)</label>
                    <select name="duration" class="form-control"><option value="5">5 min</option><option value="10">10 min</option><option value="15" selected>15 min</option><option value="30">30 min</option></select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-qrcode"></i> Generate Code</button>
        </form>
    </div>
    
    <!-- Active Sessions -->
    <?php foreach ($activeSessions as $s): ?>
    <?php if (strtotime($s['end_time']) > time()): ?>
    <div class="attendance-code-display" style="margin-bottom:var(--space-xl);">
        <p style="color:var(--text-muted);margin-bottom:var(--space-sm);"><?= htmlspecialchars($s['subject']) ?></p>
        <div class="attendance-code"><?= $s['session_code'] ?></div>
        <div class="attendance-timer" data-end="<?= $s['end_time'] ?>">
            <i class="fas fa-clock"></i> <span class="timer-text">Calculating...</span>
        </div>
        <p style="font-size:var(--text-xs);color:var(--text-muted);margin-top:var(--space-md);">Share this code with students</p>
    </div>
    <?php endif; ?>
    <?php endforeach; ?>
    
    <?php else: ?>
    <!-- Student: Enter Code -->
    <div class="card" style="max-width:500px;margin:0 auto var(--space-xl);text-align:center;">
        <h3 style="margin-bottom:var(--space-lg);">Enter Session Code</h3>
        <form method="POST">
            <input type="hidden" name="mark_attendance" value="1">
            <div class="attendance-input">
                <?php for ($i = 0; $i < 6; $i++): ?>
                <input type="text" maxlength="1" class="code-digit" data-index="<?= $i ?>" oninput="handleCodeInput(this)" onkeydown="handleCodeKeydown(event, this)">
                <?php endfor; ?>
            </div>
            <input type="hidden" name="session_code" id="sessionCode">
            <button type="submit" class="btn btn-primary btn-lg" style="margin-top:var(--space-md);"><i class="fas fa-check-circle"></i> Mark Attendance</button>
        </form>
    </div>
    <?php endif; ?>

    <!-- History -->
    <div class="card">
        <h3 style="margin-bottom:var(--space-lg);">Attendance History</h3>
        <?php if (empty($history)): ?>
        <div class="empty-state" style="padding:var(--space-xl);"><i class="fas fa-history"></i><h3>No records yet</h3></div>
        <?php else: ?>
        <div class="table-container">
            <table class="table">
                <thead><tr>
                    <th>Subject</th>
                    <th><?= isStudent() ? 'Faculty' : 'Students' ?></th>
                    <th>Code</th>
                    <th>Date</th>
                </tr></thead>
                <tbody>
                <?php foreach ($history as $h): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($h['subject']) ?></strong></td>
                    <td><?= isStudent() ? htmlspecialchars($h['faculty_name']) : ($h['student_count'] . ' students') ?></td>
                    <td><span class="text-mono badge badge-primary"><?= $h['session_code'] ?></span></td>
                    <td><?= formatDateTime(isStudent() ? $h['marked_at'] : $h['start_time']) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
