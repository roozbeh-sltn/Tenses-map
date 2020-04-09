<div id="wpbody" role="main">
  <div id="wpbody-content" aria-label="主要內容" tabindex="0">
    <div class="wrap" style="max-width: 560px;">
      <h1>Woodpecker - Click to Translate</h1>
      <br/>
      <form method="post">
        <input type="hidden" name="clearCache" value="1"/>
        <?php wp_nonce_field( 'wl_click_to_translate_verify', 'wl_click_to_translate_nonce' ); ?>
        <?php submit_button("Click to clear cache to get the latest version of the script"); ?>
      </form>
      <form method="post">
        <h3>Access Key</h3>
        <label>
          access token
          <input type="text" name="accessToken" value="<?php echo esc_html($accessToken); ?>" class="regular-text code">
          <br/>
        </label>
        (Don't have an access token? Get yours <a href="<?php echo esc_url($websiteUrl); ?>" target="_blank">here</a>.)
        <br/>
        <br/>
        <hr/>
        <h3>Enable / Disable</h3>
        <label>
          <input type="checkbox" name="autoload" <?php if($autoload) echo "checked";?> />
          Power on Click to Translate.
          <br/>
        </label>
        <br/>
        <label>
          <input type="checkbox" name="enable"
            <?php if($enable) echo "checked";?>
          />
          Enable Click to Translate functionality to new user by default.
          <br/>
        </label>
        <br/>
        <br/>
        <hr/>
        <h3>Location of the Click to Translate button</h3>
        <label>
          <input type="checkbox" name="addButtonOnContentHead"
            <?php if($addButtonOnContentHead) echo "checked";?>
          />
          Add Click to Translate button above the content
          <br/>
        </label>
        <br/>
        <div class="card">
          <p>If the option above is checked, then the Click to Translate button will appear once on a page under the main heading.</p>
          <br/>
          <p>You can add additional buttons in <a href="/wp-admin/widgets.php" style="font-size: 1.3em;"><b>Widgets</b></a> or add them on the page with this code: </p>
          <p><b style="font-size: 1.3em;">[wl-lookup button]</b></p>
          <br/>
          You can also use
          <p><b>&lt;?php echo do_shortcode('[wl-lookup button]');&nbsp;?&gt;</b></p>
          in your templates.
        </div>
        <br/>
        <ul>
          <h4>Enable Click to Translate in the following content types</h4>
          <?php foreach ($possiblePostTypes as $postType) : ?>
            <li>
              <label>
                <input name="enabledTypes[]"
                       value="<?php echo esc_html($postType); ?>"
                      type="checkbox"
                  <?php if(in_array($postType, $enabledTypes)) echo "checked";?>
                />
                <?php echo esc_html(ucfirst($postType)); ?>
                <br/>
              </label>
            </li>
          <?php endforeach ?>
        </ul>
        <br/>
        <hr/>
        <section>
        <ul>
          <h4>Enabled languages</h4>
          <?php foreach ($possibleFromLanguages as $languageCode => $language) : ?>
            <li>
              <label>
                <input name="enabledFromLanguages[]"
                       value="<?php echo esc_html($languageCode); ?>"
                      type="checkbox"
                  <?php if(in_array($languageCode, $enabledFromLanguages)) echo "checked";?>
                />
                <?php echo esc_html(ucfirst($language["name"])); ?>
                <br/>
              </label>
            </li>
          <?php endforeach ?>
        </ul>
        </section>
        <?php wp_nonce_field( 'wl_click_to_translate_verify', 'wl_click_to_translate_nonce' ); ?>
        <?php submit_button(); ?>
      </form>
    </div>
    <div class="clear"></div>
  </div><!-- wpbody-content -->
  <div class="clear"></div>
</div>
