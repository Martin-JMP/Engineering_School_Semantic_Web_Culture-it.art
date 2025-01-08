import aiohttp
import asyncio

# Set limits for concurrent requests and timeout
start_id = 0
end_id = 491149
timeout_duration = 10  # seconds
max_concurrent_requests = 50

# Variables globales pour le récapitulatif
collected_count = 0
incomplete_count = 0

# Semaphore to limit the number of concurrent requests
semaphore = asyncio.Semaphore(max_concurrent_requests)

# Fonction pour vérifier si un objet a toutes les informations requises
async def check_object(session, object_id):
    global collected_count, incomplete_count
    object_url = f"https://collectionapi.metmuseum.org/public/collection/v1/objects/{object_id}"
    try:
        async with semaphore:  # Limit concurrent requests
            async with session.get(object_url, timeout=timeout_duration) as response:
                object_data = await response.json()

                # Vérification des données requises
                required_fields = [
                    "primaryImage","title", "artistDisplayName", "artistNationality",
                    "artistBeginDate", "artistEndDate", "objectEndDate",
                    "medium", "department", "culture",
                    "dimensions", "country", "repository"
                ]

                primary_image = object_data.get("primaryImage")
                title = object_data.get("title")
                artist_display_name = object_data.get("artistDisplayName")
                
                if all(field in object_data and object_data[field] for field in required_fields):
                    collected_count += 1
                    print(f"Object ID {object_id} has an image, Title: {title}, Artist: {artist_display_name}")
                    return {
                        "object_id": object_id,
                        "title": object_data["title"],
                        "artistDisplayName": object_data["artistDisplayName"],
                        "artistNationality": object_data["artistNationality"],
                        "artistBeginDate": object_data["artistBeginDate"],
                        "artistEndDate": object_data["artistEndDate"],
                        "objectEndDate": object_data["objectEndDate"],
                        "medium": object_data["medium"],
                        "department": object_data["department"],
                        "culture": object_data["culture"],
                        "dimensions": object_data["dimensions"],
                        "country": object_data["country"],
                        "repository": object_data["repository"]
                    }
                else:
                    incomplete_count += 1
                    
    except aiohttp.ContentTypeError:
        print(f"Error: Object ID {object_id} returned non-JSON response")
    except asyncio.TimeoutError:
        print(f"Error: Object ID {object_id} request timed out")
    except Exception as e:
        print(f"Unexpected error for Object ID {object_id}: {e}")

# Fonction principale asynchrone pour récupérer les objets avec données complètes
async def fetch_objects_with_images():
    async with aiohttp.ClientSession() as session:
        tasks = [check_object(session, object_id) for object_id in range(start_id, end_id + 1)]
        results = await asyncio.gather(*tasks)
        return [obj for obj in results if obj]

# Exécuter la fonction principale
objects_with_images = asyncio.run(fetch_objects_with_images())

# Sauvegarde des résultats dans un fichier texte avec titres de colonnes
output_file = "objects_with_images222.txt"
with open(output_file, "w", encoding="utf-8") as f:
    # Écrire les titres des colonnes
    f.write(
        "Object ID\tTitle\tArtist Display Name\tArtist Nationality\tArtist Begin Date\tArtist End Date\tObject End Date\t"
        "Medium\tDepartment\tObject Name\tCulture\tDimensions\tCountry\tRepository\n"
    )
    # Écrire les données
    for obj in objects_with_images:
        f.write(
            f"{obj['object_id']}\t{obj['title']}\t{obj['artistDisplayName']}\t{obj['artistNationality']}\t"
            f"{obj['artistBeginDate']}\t{obj['artistEndDate']}\t{obj['objectEndDate']}\t{obj['medium']}\t"
            f"{obj['department']}\t{obj['culture']}\t{obj['dimensions']}\t"
            f"{obj['country']}\t{obj['repository']}\n"
        )



# Afficher le récapitulatif
print(f"Extraction terminée.")
print(f"Nombre d'œuvres avec toutes les données disponibles : {collected_count}")
print(f"Nombre d'œuvres sans toutes les données requises : {incomplete_count}")
print(f"Résultats enregistrés dans '{output_file}'")
