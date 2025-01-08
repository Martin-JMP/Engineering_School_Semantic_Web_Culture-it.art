import aiohttp
import asyncio

# Set limits for concurrent requests and timeout
start_id = 0
end_id = 491149
timeout_duration = 10  # seconds
max_concurrent_requests = 50

# Semaphore to limit the number of concurrent requests
semaphore = asyncio.Semaphore(max_concurrent_requests)

# Fonction pour vérifier si un objet a une primaryImage et récupérer les informations supplémentaires
async def check_object(session, object_id):
    object_url = f"https://collectionapi.metmuseum.org/public/collection/v1/objects/{object_id}"
    try:
        async with semaphore:  # Limit concurrent requests
            async with session.get(object_url, timeout=timeout_duration) as response:
                object_data = await response.json()
                # Check if primaryImage, title, and artistDisplayName are all present
                primary_image = object_data.get("primaryImage")
                title = object_data.get("title")
                artist_display_name = object_data.get("artistDisplayName")
                
                if primary_image and title and artist_display_name:
                    print(f"Object ID {object_id} has an image, Title: {title}, Artist: {artist_display_name}")
                    return {
                        "object_id": object_id,
                        "title": title,
                        "artistDisplayName": artist_display_name
                    }
    except aiohttp.ContentTypeError:
        print(f"Error: Object ID {object_id} returned non-JSON response")
    except asyncio.TimeoutError:
        print(f"Error: Object ID {object_id} request timed out")
    except Exception as e:
        print(f"Unexpected error for Object ID {object_id}: {e}")

# Fonction principale asynchrone pour récupérer tous les objectID avec images et infos complètes
async def fetch_objects_with_images():
    async with aiohttp.ClientSession() as session:
        tasks = [check_object(session, object_id) for object_id in range(start_id, end_id + 1)]
        results = await asyncio.gather(*tasks)
        return [obj for obj in results if obj]

# Exécuter la fonction principale
objects_with_images = asyncio.run(fetch_objects_with_images())

# Sauvegarde des résultats dans un fichier texte
with open("objects_with_images.txt", "w", encoding="utf-8") as f:
    for obj in objects_with_images:
        f.write(f"{obj['object_id']}\n")

print("Extraction terminée. Résultats enregistrés dans 'objects_with_images.txt'")
