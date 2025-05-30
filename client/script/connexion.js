function validerNomUtilisateurAvance(input) {
  const value = input.value;
  
  // Formats acceptés avec regex
  const formats = [
    /^[a-zà-ÿ]{1}[a-zà-ÿ\-']+\s?[a-zà-ÿ\-']+$/i, // Initiale prénom + nom
    /^[a-zà-ÿ\-']+\s[a-zà-ÿ]{1}$/i,               // Nom + initiale prénom
  ];
  
  const isValid = formats.some(regex => regex.test(value));
  
  if (!isValid) {
    // Afficher un message d'erreur ou nettoyer la valeur
    input.setCustomValidity("Format invalide. Exemples acceptés : dupontj, jdupont");
  } else {
    input.setCustomValidity("");
  }
}