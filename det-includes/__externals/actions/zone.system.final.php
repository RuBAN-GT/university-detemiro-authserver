<?php
    detemiro::actions()->add(array(
        'code'     => 'auth.sysLog',
        'function' => function() {
            if(openlog('vsusso', LOG_PID, LOG_LOCAL0)) {
                if($messages = detemiro::messages()->getType('auth', 'error,info')) {
                    foreach($messages as $message) {
                        $date = date('Y-m-d H:i:s', strtotime($message->date));

                        $out  = "[{$message->title}]: {$message->text}";

                        syslog((($message->status == '1') ? LOG_ERR : LOG_NOTICE), $out);
                    }
                }

                closelog();
            }
        }
    ));
?>