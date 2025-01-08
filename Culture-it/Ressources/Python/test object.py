import requests
import time

# URL pour récupérer la liste complète des objectIDs
objects_url = "https://collectionapi.metmuseum.org/public/collection/v1/objects"
response = requests.get(objects_url)
all_object_ids = response.json().get("objectIDs", [])

# Liste pour stocker les IDs avec une primaryImage
objects_with_images = []

# Compteur pour suivre la progression
total_objects = len(all_object_ids)
print(f"Nombre total d'objets à traiter: {total_objects}")

# Parcourir tous les objectIDs
#for idx, object_id in enumerate(all_object_ids):
    # URL pour chaque objet
    #object_url = f"https://collectionapi.metmuseum.org/public/collection/v1/objects/{object_id}"
    #object_data = requests.get(object_url).json()

    #print(idx)
    
    # Vérification si primaryImage est présent et non vide
    #if object_data.get("primaryImage"):
    #    objects_with_images.append(object_id)
    #    print("Photo")
    
    # Affichage de progression
    #if (idx + 1) % 100 == 0:
    #    print(f"{idx + 1} objets traités sur {total_objects}...")
    
    # Pause pour éviter de surcharger le serveur (facultatif, utile si trop de requêtes)
    #time.sleep(0.1)

# Sauvegarde des résultats dans un fichier texte
#with open("objects_with_images.txt", "w") as f:
#    for object_id in objects_with_images:
#        f.write(f"{object_id}\n")

#print("Extraction terminée. Résultats enregistrés dans 'objects_with_images.txt'")
