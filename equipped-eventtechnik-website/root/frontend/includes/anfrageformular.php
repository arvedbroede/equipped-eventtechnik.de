<section class="anfrage" id="anfrage" aria-labelledby="anfrage-heading">
      <div class="container">
        <h3 id="anfrage-heading">Jetzt anfragen</h3>
        <p class="anfrage-sub">Unverbindlich & einfach – wir melden uns schnellstmöglich bei dir.</p>

        <div id="anfrage-danke" style="display: none;">
          <p><strong>Vielen Dank!</strong> Wir haben deine Anfrage erhalten und melden uns bald bei dir.</p>
        </div>
        <form id="anfrage-form" class="anfrage-form" role="form" aria-label="Anfrageformular">
          <div class="form-group">
            <label for="name">Name *</label>
            <input type="text" id="name" name="name" required placeholder="Max Mustermann" />
          </div>
          <div class="form-group">
            <label for="email">E-Mail *</label>
            <input type="email" id="email" name="email" required placeholder="max@example.com" />
          </div>
          <div class="form-group">
            <label for="telefon">Telefon</label>
            <input type="tel" id="telefon" name="telefon" placeholder="+49 123 4567890" />
          </div>
          <div class="form-group">
            <label for="plz">Postleitzahl</label>
            <input type="text" id="plz" name="plz" placeholder="71679" pattern="\d{5}" />
          </div>
          <div class="form-group">
            <label for="termin">Wunschtermin *</label>
            <input type="date" id="termin" name="termin" required />
          </div>
          <div class="form-group">
            <label for="bundle">Bundle wählen</label>
            <select id="bundle" name="bundle">
              <option value="">– Bitte wählen –</option>
              <option value="stereo">Stereo Bundle</option>
              <option value="party">Party Bundle</option>
              <option value="festival">Festival Bundle</option>
              <option value="dj">DJ Bundle</option>
            </select>
          </div>
          <div class="form-group">
            <label for="einzelteile">Zusätzliche Einzelteile</label>
            <select id="einzelteile" name="einzelteile">
              <option value="">– Optional –</option>
              <option value="Sony GTK-XB72">Sony GTK-XB72</option>
              <option value="Hughes & Kettner Classic Line C152">Hughes & Kettner Classic Line C152</option>
              <option value="Magnum Standard - Multifunktionsbox">Magnum Standard - Multifunktionsbox</option>
              <option value="Endstufe">Endstufe</option>
              <option value="the t.mix xmix 1202 FXMP USB">the t.mix xmix 1202 FXMP USB</option>
              <option value="Mixars Primo">Mixars Primo</option>
              <option value="Aokeo Mikrofon">Aokeo Mikrofon</option>
              <option value="Stairville CLB4 RGB Compact LED Bar 4">Stairville CLB4 RGB Compact LED Bar 4</option>
              <option value="Accu Stand Pro Event Table 2 DJ Tisch">Accu Stand Pro Event Table 2 DJ Tisch</option>
              <option value="Millenium Laptopstand White">Millenium Laptopstand White</option>
            </select>
          </div>
          <div class="form-group" style="grid-column: 1 / -1;">
            <label for="nachricht">Nachricht / Anmerkungen</label>
            <textarea id="nachricht" name="nachricht" rows="4" placeholder="Gibt es etwas, das wir wissen sollten?"></textarea>
          </div>
          <div class="form-group" style="grid-column: 1 / -1;">
            <label for="datenschutz-checkbox" class="checkbox-label">
              <input type="checkbox" id="datenschutz-checkbox" name="datenschutz" required aria-describedby="ds-hinweis" />
              Ich habe die <a href="datenschutz.php" target="_blank">Datenschutzerklärung</a> gelesen und stimme der Verarbeitung meiner Daten zu.
            </label>
            <small id="ds-hinweis" style="display: block; margin-top: 0.25rem; font-size: 0.85rem;">
              Deine Angaben werden verschlüsselt übermittelt und nur zur Bearbeitung deiner Anfrage verwendet.
            </small>
          </div>
          <button type="submit" class="btn">Anfrage senden</button>
        </form>
      </div>
    </section>