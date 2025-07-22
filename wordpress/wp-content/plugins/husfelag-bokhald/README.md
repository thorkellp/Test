# Húsfélags Bókhald - WordPress Plugin

Bókhaldsforrit fyrir lítil húsfélög sem WordPress plugin. Einfalt í notkun og öflugt í virkni.

## Eiginleikar

### 📊 Fjárhagsstjórnun
- **Tekjur og gjöld** - Skráning og flokkun allra fjárhagsfærslna
- **Mánaðargjöld** - Sjálfvirkur útreikningur og úthlutun á íbúðir
- **Greiðslustjórnun** - Fylgst með greiddum og ógreiddum gjöldum

### 🏠 Íbúðaskrá
- **Íbúðarstjórnun** - Skrá íbúðir með eigendum og tengiliðaupplýsingum
- **Hlutdeild** - Útreikningur gjalda eftir hlutdeild í húsfélaginu
- **Fermetrafjöldi** - Skráning og stjórnun fermetrafjölda

### 📈 Skýrslur og yfirlit
- **Fjárhagsskýrslur** - Ársyfirlit og mánaðarleg þróun
- **Flokkun gjalda** - Sundurliðun eftir tegundum og flokkum
- **Útprentanlegar skýrslur** - Tilbúnar fyrir húsfélagsfundi

### 🎯 Notendavænt viðmót
- **WordPress samþætting** - Fullkomin samþætting við WordPress
- **Responsive hönnun** - Virkar á öllum tækjum
- **Íslenskt viðmót** - Allt á íslensku

## Uppsetning

### Kröfur
- WordPress 5.0 eða nýrri
- PHP 7.4 eða nýrri
- MySQL 5.6 eða nýrri

### Uppsetningarferli

1. **Hlaða upp plugin**
   ```
   1. Pakkaðu möppunni 'husfelag-bokhald' í zip skrá
   2. Farðu í WordPress admin → Plugins → Add New → Upload Plugin
   3. Veldu zip skrána og smelltu á "Install Now"
   4. Virkjaðu plugin-ið
   ```

2. **Grunnstillingar**
   - Plugin-ið býr til nauðsynlegar gagnagrunnstöflur sjálfkrafa
   - Farðu í "Húsfélags Bókhald" í admin menu til að byrja

3. **Fyrstu skref**
   1. Farðu í "Íbúðaskrá" og skráðu allar íbúðir
   2. Settu upp hlutdeild fyrir hverja íbúð
   3. Byrjaðu að skrá fjárhagsfærslur

## Notkun

### Íbúðaskrá
- Smelltu á "Íbúðaskrá" í menu
- Bættu við nýjum íbúðum með eigendum og hlutdeild
- Hlutdeild er gefin upp sem tugabrot (t.d. 0.0245 fyrir 2,45%)

### Fjárhagsfærslur
- Farðu í "Fjárhagsfærslur" 
- Skráðu tekjur og gjöld með viðeigandi flokkun
- Hægt að tengja færslur við tilteknar íbúðir

### Mánaðargjöld
- Smelltu á "Mánaðargjöld"
- Útbúðu mánaðargjöld með heildarupphæð
- Gjöld skiptast sjálfkrafa eftir hlutdeild íbúða
- Merktu gjöld sem greidd þegar greiðsla berst

### Skýrslur
- Farðu í "Skýrslur" fyrir fjárhagsskýrslur
- Veldu tímabil (ár eða mánuð)
- Prentaðu skýrslur fyrir fundi

## Gagnagrunnsskipan

Plugin-ið býr til þrjár töflur:

- `wp_hb_ibuddir` - Íbúðarupplýsingar
- `wp_hb_faerslur` - Fjárhagsfærslur
- `wp_hb_manadargjold` - Mánaðargjöld

## Þróun og stuðningur

### Mappauppbygging
```
husfelag-bokhald/
├── husfelag-bokhald.php    # Aðal plugin skrá
├── includes/               # Admin síður
│   ├── admin-main.php
│   ├── admin-ibudaskra.php
│   ├── admin-faerslur.php
│   ├── admin-gjold.php
│   └── admin-skyrslur.php
├── assets/
│   ├── css/
│   │   └── admin.css       # Admin stílar
│   └── js/
│       └── admin.js        # Admin JavaScript
└── README.md
```

### Framtíðarþróun
- [ ] Email tilkynningar fyrir ógreidd gjöld
- [ ] PDF útflutningur fyrir skýrslur
- [ ] Móttökukvittanir fyrir greiðslur
- [ ] Samþætting við íslenska banka
- [ ] Öryggisafritun gagna

## Leyfi

GPL v2 eða nýrri

## Höfundur

Búið til fyrir íslensk húsfélög með áherslu á einfaldleika og virkni.
