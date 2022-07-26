<?php

declare(strict_types=1);

namespace Commands\Command;

trait AbstractCommandsTraitMethods {

    /////////////////////////////////////
    ////////// Private Methods //////////
    /////////////////////////////////////

    private function leadingZeros(int $input = null, int $length = 2): ?string {
        if(is_null($input) || empty($length)) { return null; }
        $str_length = strlen((string) $input);
        $diff = $length - $str_length;
        if($diff < 0) { return null; }
        if($diff == 0) { return (string) $input; }
        for($i = 0; $i < $diff; $i++) { (string) $input = '0'.(string) $input; }
        return (string) $input;
    }

    private function measuredTime($input): ?string {
        $return = $this->leadingZeros($input->y,4).'-'.
                  $this->leadingZeros($input->m,2).'-'.
                  $this->leadingZeros($input->d,2).' '.
                  $this->leadingZeros($input->h,2).':'.
                  $this->leadingZeros($input->i,2).':'.
                  $this->leadingZeros($input->s,2).
                  substr((string) $input->f,1);
        return $return;
    }
}
