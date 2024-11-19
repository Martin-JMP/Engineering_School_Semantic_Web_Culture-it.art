# Lire le fichier contenant les nombres
with open('objects_with_images.txt', 'r') as file:
    ids = file.read().splitlines()

# Créer les instructions SQL pour chaque ID
with open('insert_statements.txt', 'w') as sql_file:
    for object_id in ids:
        sql = f"INSERT INTO Art (Object_id, Place) VALUES ({object_id}, 'Metropolitan Museum of Art');\n"
        sql_file.write(sql)

print("Les instructions SQL ont été générées dans le fichier 'insert_statements.sql'")
