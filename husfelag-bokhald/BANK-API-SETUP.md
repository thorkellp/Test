# Banka API Uppsetning - Húsfélags Bókhald

## 🏦 Tengja við íslenska banka

Plugin-ið styður nú tengingu við íslenska banka með PSD2 Account Information Services (AIS).

### Studdir bankar:
- ✅ **Íslandsbanki**
- ✅ **Arion banki** 
- ✅ **Landsbankinn**

---

## 📋 Uppsetningarferli

### 1. **Skráning sem Third Party Provider (TPP)**

Til að fá aðgang að banka API þarftu að skrá þig sem TPP:

1. **Hafa samband við bankann þinn**
   - Ring í fyrirtækjaþjónustu bankans
   - Spurðu um "PSD2 API aðgang" eða "Open Banking"
   - Segðu að þú viljir skrá þig sem Account Information Service Provider (AISP)

2. **Fylltu út umsókn**
   - Þú þarft að vera með löggiltan rekstur (húsfélag telur)
   - Uppgefðu að þú viljir nota API fyrir bókhald húsfélags
   - Þú gætir þurft að sýna fram á öryggisstöðla

3. **Fáðu API lykla**
   - Client ID
   - Client Secret
   - Endpoint URLs (ef þeir eru ekki standard)

### 2. **Stilla API í plugin-inu**

1. Farðu í **WordPress Admin → Húsfélags Bókhald → Banka API**

2. Veldu þinn banka úr dropdown

3. Sláðu inn API upplýsingar:
   - **Client ID** - Fengið frá bankanum
   - **Client Secret** - Fengið frá bankanum  
   - **Reikningsnúmer** - Bankareikningur húsfélagsins

4. Smelltu á **"Vista stillingar"**

### 3. **Prófa tengingu**

1. Smelltu á **"Prófa API tengingu"**
   - Ef þetta virkar ertu kominn með grunn tengingu

2. Smelltu á **"Sækja bankafærslur"**
   - Þetta sækir færslur síðustu 30 daga
   - Færslur eru flokkaðar sjálfkrafa

---

## 🔄 Sjálfvirk samstilling

Plugin-ið keyrir sjálfkrafa **daglega samstillingu** sem:

- ✅ Sækir nýjar bankafærslur síðustu 7 daga
- ✅ Flokkar færslur sjálfkrafa eftir lýsingu
- ✅ Kemur í veg fyrir tvítekningar
- ✅ Skráir allar villur í log

### Flokkunarreglur:

Plugin-ið flokkar bankafærslur sjálfkrafa:

| Lýsing inniheldur | Flokkur |
|------------------|---------|
| "húsfélag", "mánaðargjald" | Mánaðargjöld |
| "rafmagn" | Rafmagn |
| "hiti", "hitaveita" | Hiti |
| "vatn" | Vatn |
| "trygging" | Tryggingar |
| "viðhald", "lagfæring" | Viðhald |
| Annað | Annað |

---

## 🛠️ Vandamál og lausnir

### **"API stillingar vantar"**
- Athugaðu að Client ID og Secret séu rétt
- Prófaðu að vista stillingarnar aftur

### **"Villa við að tengjast banka"**
- Athugaðu internetsamband
- Bankinn gæti verið í viðhaldi
- API lyklarnir gætu verið úreltir

### **"Consent vantar"**
- Þú þarft að fara í gegnum OAuth2 flow hjá bankanum
- Þetta þarf að gera í browser með innskráningu

### **"Engar færslur fundust"**
- Athugaðu hvort reikningsnúmerið sé rétt
- Kannski eru engar nýjar færslur
- Athugaðu dagsetningu síðustu samstillingar

---

## 🔒 Öryggi og persónuvernd

### **Hvað er vistað:**
- ✅ API lyklar eru vistaðir dulkóðaðir í WordPress
- ✅ Aðeins bankafærslur eru sóttar (ekki persónuupplýsingar)
- ✅ Öll samskipti fara í gegnum HTTPS

### **Hvað er EKKI vistað:**
- ❌ Innskráningarupplýsingar í netbanka
- ❌ Kortnúmer eða PIN kóðar  
- ❌ Persónuupplýsingar um íbúa

### **Aðgangsstýring:**
- Aðeins WordPress admin notendur geta stillt API
- Allir API köll eru með nonce verification
- Rate limiting kemur í veg fyrir misnotkun

---

## 📊 Kostir API tengingar

### **Fyrir húsfélagið:**
- 🚀 **Sjálfvirk bókhald** - Engin handvirk innsláttur
- 📈 **Rauntíma gögn** - Alltaf nýjustu upplýsingar
- ⏰ **Tímasparnir** - Mínútur í stað klukkustunda
- 📋 **Nákvæmari skýrslur** - Engar innsláttarvillur

### **Fyrir stjórn:**
- 📊 Betri fjárhagsstýring
- 🔍 Auðveldari eftirlit
- 📈 Greiðari fundi með nýjustu gögnum
- 💰 Betri kostnaðarstýring

---

## 🤝 Stuðningur

### **Ef þú þarft hjálp:**

1. **Athugaðu fyrst:**
   - Er bankinn þinn studdur?
   - Eru API lyklarnir réttir?
   - Er internetsamband í lagi?

2. **Skoðaðu sync log:**
   - Farðu í Banka API síðuna
   - Skoðaðu "Síðustu samstillingar"
   - Athugaðu hvort villur séu skráðar

3. **Hafðu samband:**
   - Email: support@example.com
   - Hafðu API error messages tilbúnar
   - Nefndu hvaða banka þú ert að nota

### **Algengar spurningar:**

**S: Kostar þetta eitthvað hjá bankanum?**  
A: PSD2 API ætti að vera ókeypis fyrir löggilta TPP aðila.

**S: Hvað ef bankinn minn er ekki studdur?**  
A: Hafðu samband við okkur - við getum bætt honum við.

**S: Er þetta öruggt?**  
A: Já, þetta notar sömu öryggisstaðla og netbankar.

**S: Hvað ef ég skipti um banka?**  
A: Þú þarft bara að uppfæra API stillingarnar.

---

## 🔮 Framtíðarþróun

Við erum að vinna í:

- [ ] **Payment Initiation** - Sjálfvirkar greiðslur
- [ ] **Fleiri bankar** - Sparisjóðir og lánafélög  
- [ ] **Betri flokkun** - Machine learning fyrir færslur
- [ ] **Email tilkynningar** - Þegar nýjar færslur koma
- [ ] **Mobile app** - Skoða stöðu á símanum

---

*Síðast uppfært: Júlí 2025*
