<?php
	// Exit if accessed directly
	if ( ! defined( 'ABSPATH' ) ) {
		wp_die( __( 'Directly access this file you can not!' ) );
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'Sufficient permissions to access this page you have not.' ) );
	}
	$options = get_option(
			'de.bessermitfahren.options',
			array(
					'place_from' => 'Berlin',
					'place_to' => '',
					'agb_accepted' => false,
					'api_key' => false
			)
	);
	$changed = false;

	if ( isset( $_POST['get_new_api_key']) && $_POST['get_new_api_key'] == 'true') {
		$options['api_key'] = bmf_get_api_key();
	} else {


		// AGB have been accepted
		if ( isset( $_POST['bow_to_rules'] ) && ! empty( $_POST['bow_to_rules'] ) ) {
			$options['agb_accepted'] = true;
			$changed = true;
		}

		if (
				isset($_POST['bmf_api_key']) &&
				strtolower(trim( $_POST['bmf_api_key'] )) != strtolower( trim( $options['api_key'] ) )
		) {
			$options['api_key'] = sanitize_text_field( strtolower(trim( $_POST['bmf_api_key'] ) ));
			$changed = true;
		}

		if (
				isset($_POST['bmf_from']) &&
				strtolower(trim( $_POST['bmf_from'] )) != strtolower( trim( $options['place_from'] ) )
		) {
			$options['place_from'] = sanitize_text_field( strtolower(trim( $_POST['bmf_from'] ) ));
			$changed = true;
		}

		if (
				isset($_POST['bmf_to']) &&
				strtolower( trim( $_POST['bmf_to'] ) ) != strtolower( trim( $options['place_to'] ) )
		) {
			$options['place_to'] = sanitize_text_field( strtolower( trim( $_POST['bmf_to'] ) ) );
			$changed = true;
		}

		if (
				isset($_POST['bmf_extra_style']) &&
				strtolower( trim( $_POST['bmf_extra_style'] ) ) != strtolower( trim( $options['extra_style'] ) )
		) {
			$options['extra_style'] = sanitize_text_field( strtolower( trim( $_POST['bmf_extra_style'] ) ) );
			$changed = true;
		}
	}

	update_option( 'de.bessermitfahren.options', $options );
?>
<div class="wrap">
	<h1>Einstellungen › Mitfahrgelegenheiten</h1>
	<?php if ( !$options['api_key'] ) { ?>
		<div class="error notice">
			<p>
				<strong>
					<?php _e( 'Das Plugin ist noch nicht eingerichtet (siehe unten)!' ) ?>
				</strong>
			</p>
		</div>
	<?php } ?>
	<?php if ( ! $options['agb_accepted'] && $options['api_key'] ) { ?>
		<div class="error notice">
			<p>
				<strong>
					<?php _e( 'Bitte bestätige unsere Regeln (siehe unten).' ) ?>
				</strong>
			</p>
		</div>
	<?php } ?>
	<?php if ( $changed ) { ?>
		<div class="updated notice is-dismissible">
			<p>
				<strong>
					<?php _e( 'Änderungen gespeichert.' ) ?>
				</strong>
			</p>
			<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
		</div>
	<?php } ?>
	<p>
		<img style="border: 2px solid black; max-width: 320px" align="right"
		     src="<?php echo plugins_url( '/wordpress_example.png', __FILE__ ); ?>" alt="so könnte es aussehen"/>
		Das BesserMitfahren.de Mitfahrgelegenheiten Plugin erlaubt Dir auf Deiner eigenen Seite eine Suche anzuzeigen.<br>
		Außerdem kannst Du eine immer aktuelle Liste der Fahrten von oder zu einem Ort deiner Wahl anzeigen.
	</p>
	<?php if ( ! $options['api_key'] ) { ?>
		<p>
			Dein API-Key hätte eigentlich beim aktivieren des Plugins automatisch für Dich erzeugt worden sein sollen, scheinbar klappte das aber nicht.
			<br />
			Du kannst den Vorgang hier nochmal starten
			<form method="post">
				<input type="hidden" name="get_new_api_key" value="true">
				<input type="submit" class="button button-primary" value="API-Key anfordern">
			</form>
			<hr />
			Wenn das nichts bewirkt, kontaktiere uns per E-Mail:
			<br />
			<a href="mailto:netzwerk@bessermitfahren.de">
				netzwerk@bessermitfahren.de
			</a>
		</p>

		<form method="post">
			<ul>
				<li>
					<label for="bmf_api_key">API Key (erforderlich)</label>
					<input type="text" id="bmf_api_key" name="bmf_api_key"
					       value="<?php echo $options['api_key']; ?>" style="width: 260px">
				</li>
				<li>
					<input type="submit" name="submit" id="bmf_submit" value="<?php _e( 'Speichern' ); ?>"
					       class="button button-primary">
				</li>
			</ul>
		</form>
	<?php } else { ?>

		<?php if ( ! $options['agb_accepted'] ) { ?>
			<div class="card">
				<h2>Beachte BesserMitfahren.de ist nicht kommerziell, es gibt keine Gebühren und keine Werbung.</h2>
				<p>
					Darum gilt:
				<ul>
					<li>Um die Fahrten von BesserMitfahren.de auf Deiner Seite anzeigen zu dürfen, darfst Du keine Gebühren für
						den
						Zugang zur Liste der Fahrten erheben.
					</li>
					<li>Auch darf keine Werbung auf der Seite mit den Fahrten geschaltet sein (wenn Du auf anderen Seiten deiner
						Webpräsenz Werbung hast, ist das natürlich dein Ding).
					</li>
				</ul>
				</p>
				<ul>
					<li>
						<form method="post">
							<input type="checkbox" name="bow_to_rules" id="rules">
							<label for="rules">
								<?php printf(__( 'Ich habe die %s und obige Regeln verstanden und halte mich dran.'), '<a href="https://www.bessermitfahren.de/impressum" target="_blank">AGB</a>'); ?>
							</label>
							<hr/>
							<input type="submit" value="<?php _e('Speichern'); ?>" class="button button-primary">
						</form>
					</li>
				</ul>
			</div>
		<?php } else { ?>
			<div class="card">
				<p>
					Einfach den Shortcode <code>[bmf_list]</code> in den Text eines Posts oder einer Seite einfügen.
					<hr>
					Du kannst Start- beziehungsweise Zielorte vorausgewählt angeben:
					<ul>
						<li>Alle Fahrten ab Berlin vorausgewählt:
							<code>[bmf_list from='Berlin']</code>
						</li>
						<li>Alle Fahrten nach Leipzig vorausgewählt:
							<code>[bmf_list to='Leipzig']</code>
						</li>
					</ul>
				</p>
			</div>
			<div class="card">
				<h2>
					<?php _e( 'Standardwerte' ); ?>
				</h2>
				<p>Du kannst einen Standardwert für Abfahrt und/oder Ziel hinterlegen. Dieser wird verwendet,
					wenn der Shortcode keine Vorgabe enthält.</p>
				<form method="post">
					<ul>
						<li>
							<label for="bmf_from">Abfahrtsort (optional)</label>
							<input type="text" id="bmf_from" name="bmf_from" value="<?php echo $options['place_from']; ?>">
						</li>
						<li>
							<label for="bmf_to">Zielsort (optional)</label>
							<input type="text" id="bmf_to" name="bmf_to" value="<?php echo $options['place_to']; ?>">
						</li>
						<li>
							<input type="submit" name="submit" id="bmf_submit" value="<?php _e( 'Speichern' ); ?>"
							       class="button button-primary">
						</li>
					</ul>
				</form>
			</div>
			<div class="card">
				<h2>
					<?php _e( 'Stylesheet anpassen' ); ?>
				</h2>
				<p>
					Da Wordpress Themes mitunter sehr unterschiedliche Basisstile haben, kommt es immer mal wieder vor, dass die Ausgabe dieses Plugins nicht optimal aussieht.
					<br />
					In diesem Fall kannst Du hier zusaetzliche CSS Regeln eintragen, die für die Liste angewendet werden sollen.
				</p>
				<form method="post">
					<ul>
						<li>
							<label for="bmf_extra_style">CSS Regeln (optional)</label>
						</li>
						<li>
							<textarea name="bmf_extra_style" id="bmf_extra_style" style="width: 100%" rows="10"><?php
									echo str_ireplace(array( '}', '} '),"}\n",$options['extra_style']);
							?></textarea>
						</li>
						<li>
							<input type="submit" name="submit" id="bmf_submit" value="<?php _e( 'Speichern' ); ?>"
							       class="button button-primary">
						</li>
					</ul>
				</form>
			</div>
			<div class="card">
				<h2>
					<?php _e('API Key'); ?>
				</h2>
				<p>
					Das ist Dein API Schlüssel. Sollte es damit Probleme geben (er zum Beispiel nicht automatisch erzeugt werden),
					dann kontaktiere uns per E-Mail:
					<br/>
					<a href="mailto:netzwerk@bessermitfahren.de">
						netzwerk@bessermitfahren.de
					</a>
				</p>
				<ul>
					<li>
						<form method="post">
							<label for="bmf_api_key">API Key (erforderlich)</label>
							<input type="text" id="bmf_api_key" name="bmf_api_key"
							       value="<?php echo $options['api_key']; ?>" style="width: 260px">
					</li>
					<li>
						<input type="submit" name="submit" id="bmf_submit" value="<?php _e( 'Speichern' ); ?>"
						       class="button button-primary">
					</li>
					</form>
					<li>
						<form method="post">
							<input type="hidden" name="get_new_api_key" value="true">
							<input type="submit" class="button button-primary" value="API-Key neu anfordern">
						</form>
					</li>
				</ul>
			</div>
		<?php } ?>
	<?php }
	if ( $options['api_key'] && $options['agb_accepted'] ) { ?>
		<script type="text/javascript">
			jQuery('#bmf_needs_configuration').remove();
		</script>
	<?php } ?>
</div>
