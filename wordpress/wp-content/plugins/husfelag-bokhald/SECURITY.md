# Öryggishandbók - Húsfélags Bókhald

## 🔒 Öryggisráðstafanir í plugin-inu

### 1. **Aðgangsöryggi**
- ✅ **Capability checks** - Aðeins admin notendur geta notað plugin-ið
- ✅ **wp_die()** ef notandi hefur ekki heimildir
- ✅ **current_user_can('manage_options')** á öllum síðum

### 2. **Form öryggi**
- ✅ **Nonce verification** - wp_verify_nonce() á öllum formum
- ✅ **CSRF vörn** - Nonce tokens á öllum aðgerðum
- ✅ **Input sanitization** - sanitize_text_field(), sanitize_email(), etc.
- ✅ **Data validation** - Athuga að gögn séu gild

### 3. **Gagnagrunnur öryggi**
- ✅ **Prepared statements** - $wpdb->prepare() fyrir allar fyrirspurnir
- ✅ **SQL injection vörn** - Engar beinar SQL fyrirspurnir
- ✅ **Foreign key constraints** - Tenging við WordPress users töflu
- ✅ **Unique constraints** - Koma í veg fyrir tvítekningar

### 4. **AJAX öryggi**
- ✅ **check_ajax_referer()** - Nonce athugun á AJAX
- ✅ **wp_send_json_error/success** - Örugg JSON svör
- ✅ **Capability checks** í öllum AJAX handlers
- ✅ **Input validation** á öllum AJAX gögnum

### 5. **Skrá öryggi**
- ✅ **.htaccess** í includes möppu - Kemur í veg fyrir beinan aðgang
- ✅ **index.php** í möppum - Kemur í veg fyrir directory browsing
- ✅ **ABSPATH check** í öllum PHP skrám
- ✅ **File access restrictions**

### 6. **Output öryggi**
- ✅ **esc_html()** fyrir HTML output
- ✅ **esc_attr()** fyrir HTML attributes
- ✅ **esc_url()** fyrir URL-s
- ✅ **wp_kses()** fyrir rich content ef þörf

### 7. **Kerfiskröfur**
- ✅ **PHP version check** - Minnst PHP 7.4
- ✅ **WordPress version check** - Minnst WP 5.0
- ✅ **Graceful degradation** ef kröfur ekki uppfylltar

### 8. **Logging og audit**
- ✅ **User ID tracking** - Hver gerði hvað
- ✅ **Timestamp tracking** - Hvenær var aðgerð framkvæmd
- ✅ **Update tracking** - Breytingar á gögnum

## 🛡️ Viðbótar öryggisráðstafanir

### Fyrir production umhverfi:

1. **SSL/HTTPS** - Alltaf nota HTTPS
2. **Strong passwords** - Krefjast sterkra lykilorða
3. **Two-factor authentication** - Ef mögulegt
4. **Regular backups** - Öryggisafrit reglulega
5. **Plugin updates** - Halda plugin-inu uppfærðu
6. **WordPress updates** - Halda WordPress uppfærðu

### Monitoring:
```php
// Bæta við error logging
if (defined('WP_DEBUG') && WP_DEBUG) {
    error_log('Húsfélags Bókhald: ' . $message);
}
```

### Rate limiting:
```php
// Koma í veg fyrir spam/brute force
$attempts = get_transient('hb_attempts_' . $user_id);
if ($attempts > 5) {
    wp_die('Of margar tilraunir. Reyndu aftur síðar.');
}
```

## 🔍 Öryggisathugun

### Checklist fyrir deployment:

- [ ] Allir form fields eru sanitized
- [ ] Allar SQL fyrirspurnir nota prepared statements  
- [ ] Öll output er escaped
- [ ] Nonce verification á öllum aðgerðum
- [ ] Capability checks á öllum síðum
- [ ] .htaccess og index.php skrár til staðar
- [ ] Error messages leka ekki næmum upplýsingum
- [ ] File upload restrictions (ef við bætum því við)
- [ ] Session security (ef við notum sessions)

## 📞 Ef þú finnur öryggisbrest

1. **Ekki birta opinberlega** - Sendu email á security@example.com
2. **Lýstu vandamálinu** - Hvað er hægt að gera
3. **Gefðu dæmi** - Hvernig á að endurtaka
4. **Bíddu eftir svari** - Við lögum það fljótt

## 🔄 Regular Security Review

- **Mánaðarlega:** Athuga WordPress og plugin uppfærslur
- **Ársfjórðungslega:** Fara yfir user permissions
- **Árlega:** Full security audit af kóðanum

---

**Athugasemd:** Þetta plugin er hannað fyrir lítil húsfélög með traustum admin notendum. Ef þú þarft fleiri öryggiseiginleika eða hefur sérstök þarfir, hafðu samband.
