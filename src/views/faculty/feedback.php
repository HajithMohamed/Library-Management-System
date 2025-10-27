<?php

require_once __DIR__ . '/../partials/header.php';

/**
 * @var array $user
 * @var array $feedbacks
 * @var array $errors
 * @var string $success
 */

?>

<div class="container">
    <h1>Contact Librarian / Send Feedback</h1>
    <hr>

    <?php if (isset($success)) : ?>
        <div class="alert alert-success">
            <?= $success ?>
        </div>
    <?php endif; ?>

    <?php if (isset($errors) && !empty($errors)) : ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $error) : ?>
                    <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-6">
            <form action="/faculty/feedback" method="post">
                <div class="form-group">
                    <label for="subject">Subject</label>
                    <input type="text" name="subject" id="subject" class="form-control">
                </div>
                <div class="form-group">
                    <label for="message">Message</label>
                    <textarea name="message" id="message" class="form-control" rows="5"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div>
        <div class="col-md-6">
            <h3>Your Previous Feedback</h3>
            <?php if (empty($feedbacks)) : ?>
                <p>No feedback submitted yet.</p>
            <?php else : ?>
                <ul class="list-group">
                    <?php foreach ($feedbacks as $feedback) : ?>
                        <li class="list-group-item">
                            <strong><?= htmlspecialchars($feedback['subject']) ?></strong>
                            <p><?= htmlspecialchars($feedback['message']) ?></p>
                            <small class="text-muted">Submitted on: <?= date('Y-m-d H:i', strtotime($feedback['createdAt'])) ?></small>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>