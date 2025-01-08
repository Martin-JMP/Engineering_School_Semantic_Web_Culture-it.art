# Define the input and output file paths
input_file = "objects_with_images222.txt"  # Replace with your input file name
output_file = "insert_statements222.txt"

# Define the SQL table name
table_name = "artworks"

# Open the input file and process the data
with open(input_file, "r", encoding="utf-8") as infile:
    # Read all lines
    lines = infile.readlines()

# Extract the header and data
header = lines[0].strip().split("\t")  # Extract column names
data = [line.strip().split("\t") for line in lines[1:]]  # Process data lines

# Map header names to SQL table column names
# (This step ensures compatibility with database naming conventions)
column_mapping = {
    "Object ID": "object_id",
    "Title": "title",
    "Artist Display Name": "artist_display_name",
    "Artist Nationality": "artist_nationality",
    "Artist Begin Date": "artist_begin_date",
    "Artist End Date": "artist_end_date",
    "Object End Date": "object_end_date",
    "Medium": "medium",
    "Department": "department",
    "Culture": "culture",
    "Dimensions": "dimensions",
    "Country": "country",
    "Repository": "repository"
}

# Replace the header with mapped column names
columns = [column_mapping[col] for col in header]

# Create the SQL INSERT statements
insert_statements = []
for row in data:
    # Ensure we only process rows with the correct number of columns
    if len(row) == len(header):
        # Escape single quotes in values and handle null values
        values = ", ".join(
            f"'{value.replace('\'', '\'\'')}'" if value else "NULL" for value in row
        )
        statement = f"INSERT INTO {table_name} ({', '.join(columns)}) VALUES ({values});"
        insert_statements.append(statement)
        print(statement)

# Write the SQL INSERT statements to the output file
with open(output_file, "w", encoding="utf-8") as outfile:
    outfile.write("\n".join(insert_statements))

print(f"SQL INSERT statements generated and saved to {output_file}")
