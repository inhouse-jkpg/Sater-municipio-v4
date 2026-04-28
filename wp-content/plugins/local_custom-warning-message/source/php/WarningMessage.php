<?php

namespace CustomWarningMessage;

class WarningMessage
{

    public function __construct()
    {
        include_once( plugin_dir_path( __FILE__ ) . 'warning-message/class-deserializer.php' );
        include_once( plugin_dir_path( __FILE__ ) . 'warning-message/class-serializer.php' );

        // Setup and initialize the class for saving our options.
        $this->serializer = new Serializer();
        $this->serializer->init();

        // Setup the class used to retrieve our option value.
        $this->deserializer = new Deserializer();

        add_action( 'network_admin_menu', array( $this, 'settings_menu' ) );
        add_action( 'admin_menu', array( $this, 'settings_menu' ) );
        add_action( 'wp_head', array( &$this, 'display' ) );
    }

    public function settings_menu() {
        $title = __( 'Varningsmeddelande', 'warning-message' );
        
        add_menu_page(
            $title,
            $title,
            'manage_options',
            'warning_message_settings',
            array( $this, 'settings_page' ),
            'dashicons-warning'
        );
    }

    public function settings_page() {
        include CUSTOMWARNINGMESSAGE_PATH . 'source/php/warning-message/settings-page.php';
    }

    public function display() {
        if($this->deserializer->get_value( 'custom-data-warningmessage-active' )) {
            $message = $this->deserializer->get_value( 'custom-data-warningmessage' );
            $link = $this->deserializer->get_value( 'custom-data-warningmessage-link' );
            $url = $this->deserializer->get_value( 'custom-data-warningmessage-link-url' );
            $type = $this->deserializer->get_value( 'custom-data-warningmessage-type' );

            // Escape the data from the database and prepend it to the post content.

/* -- Ändrningar av Nellie/Lars
if sats på fa-icon. i eller ! beroende på färg på medd.
lagt elementen i divar och p

*/ 
            ?>
                <div id="warning-message" class="warning-messsage <?php echo $type; ?>">
                    <div class="container grid">
                        <div class="grid-xs-2 grid-md-1 icon-grid">
                            <?php if($type == 'orange') : ?>
                                <span class="c-icon c-icon--warning c-icon--material c-icon--material-warning material-icons c-icon--size-xxl" role="img" aria-label="Ikon: Varning" alt="Ikon: Varning" data-nosnippet="" data-uid="664b669876d42">
                                    <span data-nosnippet="" translate="no" aria-hidden="true">
                                            warning
                                    </span>
                                </span>
                            <?php endif;?>
                            <?php if($type == 'blue') : ?>
                                <span class="c-icon c-icon--info c-icon--material c-icon--material-info material-icons c-icon--size-xxl" role="img" aria-label="Ikon: Info" alt="Ikon: Info" data-nosnippet="" data-uid="664b669876d43">
                                    <span data-nosnippet="" translate="no" aria-hidden="true">
                                            info
                                    </span>
                                </span>
                            <?php endif;?>
                        </div>
                        <div class="grid-xs-10 grid-md-11">
                            <div class="msg grid-xs-12 grid-md-11">
                                <p>
                                    <?php echo esc_html( $message ) ; ?>
                                </p>
                            </div>
                            <div class="msg grid-xs-12 grid-md-11">
                                <a class="warning-messsage-link" href="<?php echo esc_html( $url ); ?>"><?php echo esc_html( $link ); ?></a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php
        
        }
    }
}

