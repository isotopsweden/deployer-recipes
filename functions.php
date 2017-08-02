<?php

if ( ! function_exists( 'stage' ) ) {
    /**
     * Get stage.
     *
     * @return string
     */
    function stage() {
        $argv = isset( $_SERVER['argv'] ) ? $_SERVER['argv'] : [];

        return count( $argv ) > 1 && isset( $argv[2] ) ? $argv[2] : 'default';
    }
}
