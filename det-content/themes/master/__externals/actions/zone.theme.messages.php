<?php
    detemiro::actions()->add(array(
        'function' => function() {
            if($messages = detemiro::messages()->getType('auth.public')) {
                echo '<div id="messages">';
                foreach($messages as $message):
                    switch($message->status) {
                        case 1:
                            $status = 'danger';
                            break;
                        case 2:
                            $status = 'warning';
                            break;
                        case 3:
                            $status = 'success';
                            break;
                        default:
                            $status = 'info';
                    }
            ?>
                <div class="alert alert-<?=$status; ?>" role="alert">
                    <?=($message->title) ? "<h4>{$message->title}</h4>" : ""; ?>
                    <?=($message->text)  ? "<p>{$message->text}</p>" : ""; ?>
                </div>
            <?php
                endforeach;
                echo '</div>';
            }
        }
    ));
?>