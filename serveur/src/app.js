function validerRecherche(input) {
    let cleaned = input.value.replace(/[^a-zA-ZÀ-ÿ0-9\s\-']/g, '');
    const onlyDigits = cleaned.replace(/\D/g, ''); 
    if (/^\d+$/.test(cleaned.trim())) {
      cleaned = onlyDigits.slice(0, 10);
    }
  
    input.value = cleaned;
  }
  