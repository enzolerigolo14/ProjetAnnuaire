def affine_encrypt(text, a, b):
    if a % 2 == 0 or a % 13 == 0:
        raise ValueError("a doit être premier avec 26 (éviter les multiples de 2 ou 13).")

    result = ''
    for char in text:
        if char.isalpha():
            x = ord(char.upper()) - ord('A')
            encrypted = (a * x + b) % 26
            result += chr(encrypted + ord('A'))
        else:
            result += char
    return result

def affine_decrypt(cipher, a, b):
    a_inv = pow(a, -1, 26)
    result = ''
    for char in cipher:
        if char.isalpha():
            y = ord(char.upper()) - ord('A')
            decrypted = (a_inv * (y - b)) % 26
            result += chr(decrypted + ord('A'))
        else:
            result += char
    return result

texte_clair = "SECURITE"
a, b = 5, 8  
chiffre = affine_encrypt(texte_clair, a, b)
dechiffre = affine_decrypt(chiffre, a, b)

print("Texte chiffré :", chiffre)
print("Texte déchiffré :", dechiffre)
