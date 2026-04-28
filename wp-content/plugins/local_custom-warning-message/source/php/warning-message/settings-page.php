<div class="wrap">

	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<form method="post" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>">

		<div id="warning-message-container">
			<div class="options">
				<p>
					<label>Varningsmeddelande till allmänheten.</label><br/>
					<textarea name="warning-message" cols="50" rows="6"><?php echo esc_attr( $this->deserializer->get_value( 'custom-data-warningmessage' ) ); ?>	</textarea>
				</p>

				<p>
					<label>Länktext</label><br/>
					<input type="text" name="warning-message-link" value="<?php echo esc_attr( $this->deserializer->get_value( 'custom-data-warningmessage-link' ) ); ?>" />
				</p>

				<p>
					<label>Länk till mer information, antingen extern URL eller intern undersida</label><br/>
					<input type="text" name="warning-message-link-url" value="<?php echo esc_attr( $this->deserializer->get_value( 'custom-data-warningmessage-link-url' ) ); ?>" />
				</p>
<!--  Ändringar av nellie/lars
	Lagt till att man kan ändra meddelandetyp, vilket byter bakgrundsfärg (och i förlängningen icon) på varningsmeddelandet

-->
				<p>
					<label>Välj meddelandetyp</label><br/>
					<input type="radio" name="warning-message-type" value="orange" 	<?php checked(esc_attr( $this->deserializer->get_value( 'custom-data-warningmessage-type' ) ), 'orange', true); ?>  />
					<label for "orange">Varning (varningstriangel ikon)</label><span><i class="pricon pricon-notice-warning"></i></span>
					<br />
					<input type="radio" name="warning-message-type" value="blue" 	<?php checked(esc_attr( $this->deserializer->get_value( 'custom-data-warningmessage-type' ) ), 'blue', true); ?> />
					<label for "blue">Information (informations i ikon)</label><span><i class="pricon pricon-info-o"></i></span>
				</p>
<!--
				<p>
				<div>
				  <h2><span style="background-color:#ffff00">OBS! Rensa cache</span></h2>
					<p><strong>Efter att du aktiverat/avaktiverat ett varningsmeddelande och sparat ändringarna är det viktigt att du rensar webbsidans cache i Wordpress. <br />
					Detta gör du genom att gå till <span style="background-color: #444; color: #eee"> Inställningar -> WP Super Cache </span> i vänstermenyn. Under fliken Enkel klickar du på knappen <span style="background-color: #f1f1f1; color: #000; border: 1px solid #000" >Radera Cache </span> </strong></p>
				</div>
				</p>
-->
				<p>
					<label>Aktivera varningsmeddelandet för allmänheten.</label><br/>
					<input type="checkbox" name="warning-message-active" <?php checked(esc_attr( $this->deserializer->get_value( 'custom-data-warningmessage-active' ) )); ?> />
				</p>
				
			
		</div>

		<?php
			wp_nonce_field( 'warning-settings-save', 'warning-custom-message' );
			submit_button();
		?>

	</form>

</div><!-- .wrap -->
