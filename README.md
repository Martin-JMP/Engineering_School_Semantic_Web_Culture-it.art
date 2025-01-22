# Semantic E-Culture Repository

This repository contains the source code and documentation for the **Semantic E-Culture Repository**, a platform designed to preserve and enhance the accessibility of cultural heritage using Semantic Web technologies. This project was developed as part of the **II.3521 - Semantic Web and Knowledge Management** course.

## Table of Contents
- [Introduction](#introduction)
- [Features](#features)
- [System Architecture](#system-architecture)
- [Setup Instructions](#setup-instructions)
- [Ontology Creation](#ontology-creation)
- [Data Sources](#data-sources)
- [RDF Conversion and Validation](#rdf-conversion-and-validation)
- [Website Features](#website-features)
- [Ethical Considerations](#ethical-considerations)
- [Project Timeline](#project-timeline)
- [Contributors](#contributors)

## Introduction
The Semantic E-Culture Repository is a digital repository that organizes and presents cultural heritage artifacts using Semantic Web technologies. It leverages ontologies, RDF data, and SWRL rules to enable users to explore cultural data intuitively. The platform aims to enhance cultural preservation, accessibility, and user interaction with historical artifacts.

## Features
- Ontology-driven data organization.
- Integration with **Wikidata** and **The Met Museum API**.
- Dynamic user roles: Viewer, Commenter, Artist.
- RDF data storage and SPARQL querying.
- Ethical data handling and accessibility.

## System Architecture
The platform comprises the following components:
- **Frontend**: Built using HTML, CSS, and JavaScript for user interaction.
- **Backend**: PHP for server-side processing and database interaction.
- **Database**: Apache Jena Fuseki for RDF data storage.
- **Semantic Queries**: SPARQL for retrieving and manipulating RDF data.

## Setup Instructions
1. Clone the repository:
   ```bash
   git clone <repository-url>
   ```
2. Install dependencies for the backend and frontend.
3. Set up the Apache Jena Fuseki triplestore database:
   - Import the generated RDF files.
   - Configure SPARQL endpoints for querying.
4. Start the web server and access the platform via `localhost`.

## Ontology Creation
The ontology defines the structure and relationships of cultural data:
- **Classes**: Artwork, Author, Culture, Repository, User, Review.
- **Data Properties**:
  - Artwork: `hasNameArt`, `hasDescription`, `dimension`, `medium`, etc.
  - Author: `hasNameAuth`, `birthYear`, `deathYear`.
- **Object Properties**:
  - `Artwork - hasAuthor - Author`
  - `Review - aboutArtwork - Artwork`

The ontology was developed using Protégé and validated with Python's RDFLib and SHACL.

## Data Sources
Data was extracted and enriched using:
- **Wikidata Query Service** with SPARQL.
- **The Met Museum API** for comprehensive artwork details.

After extraction, data preprocessing included handling missing values and standardizing formats. The final dataset contains over 2,400 records.

## RDF Conversion and Validation
Data was converted to RDF using Python's RDFLib:
- **Namespaces** were defined and bound for RDF generation.
- Over 44,000 RDF triplets were created and validated using Protégé, OWL reasoners, and SPARQL queries.
- Custom SWRL rules were implemented to infer additional knowledge.

## Website Features
1. **User Roles**:
   - **Viewer**: Browse artworks without registration.
   - **Commenter**: Add comments after registration.
   - **Artist**: Upload and manage artworks (requires admin approval).

2. **Dynamic Pages**:
   - Explore artworks and view details.
   - Add comments and engage with the community.
   - Upload new artworks with metadata.

3. **Advanced Search**:
   - Query artworks based on attributes like artist, technique, or style.

## Ethical Considerations
- **Data Privacy**: User data is encrypted and securely stored.
- **Copyright Compliance**: Data sourced from copyright-free platforms.
- **Inclusivity**: Designed for accessibility and user engagement.
- **Community Moderation**: Ensures respectful and constructive interactions.

## Project Timeline
| Task                       | Scheduled Finish | Real Finish |
|----------------------------|------------------|-------------|
| First Group Meeting        | 01/11/2024       | 01/11/2024  |
| Scope Definition           | 06/11/2024       | 05/11/2024  |
| Creation of Ontology       | 27/11/2024       | 25/11/2024  |
| Data Extraction            | 06/12/2024       | 03/12/2024  |
| RDF Conversion             | 12/12/2024       | 10/12/2024  |
| Database Setup             | 05/01/2025       | 05/01/2025  |
| Frontend Development       | 11/01/2025       | 14/01/2025  |
| Testing and Debugging      | 13/01/2025       | 16/01/2025  |
| Demonstration              | 22/01/2025       | 22/01/2025  |


## Sources
- [Wikidata Query Service](https://query.wikidata.org/)
- [The Metropolitan Museum of Art API](https://metmuseum.github.io/)
- [Europeana](https://www.europeana.eu/en)
- [Protégé Documentation](https://protege.stanford.edu/support.php#documentationSupport)
- [Karma User Guide](https://github.com/usc-isi-i2/Web-Karma/wiki)

## Contributors
- Martin Joncourt
- Chao Zhao
- Valentyna Pronina
- Abrahán Gautiel Rodriguez
- Guilhem Frisch
- Houwei Li


https://culture-it.art/

# Culture-it.art

Use this reference code for each page you make :
Just don't forget to update the link between each page, css and js.

````
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="description" content="Web site created by Culture-It" />
    <link rel="apple-touch-icon" href="/logo192.png" />
    <title>Culture-It</title>
    <link href="../Nav_Bar/Nav_bar.css" rel="stylesheet">
</head>

<body>
    <div class="navbar">
        <div class="center-nav">
            <a href="../../" class="nav-link" data-name="Text1">Text1</a>
            <a href="../../" class="nav-link" data-name="Text2">Text2</a>
            <a href="../../" class="nav-link" data-name="Text3">Text3</a>
            <a href="../../" class="nav-link" data-name="Text4">Text4</a>
        </div>
        <div class="right-nav" id="user-section">
            <a href="../User/Login/Login.html" class="nav-link" data-name="login">Login</a>
        </div>
    </div>

    <div class = "page-wrap" style="font-size: 40px;">

    </div>

    <footer class="site-footer">
        <div class="footer-buttons">
            <a href="../../">Text1</a>
            <a href="../../">Text2</a>
            <a href="../../">Text3</a>
            <a href="../../">Text4</a>
        </div>

        <!-- For the design -->
        <div class="bubble1"></div>
        <div class="bubble2"></div>
        <div class="bubble3"></div>
        <div class="bubble4"></div>
        <div class="bubble5"></div>
        <div class="bubble6"></div>
        <div class="bubble7"></div>
        <div class="bubble8"></div>
        <div class="bubble9"></div>
        <div class="bubble10"></div>
        <div class="bubble11"></div>
        <div class="bubble12"></div>
        <div class="bubble13"></div>
        <div class="bubble14"></div>
        <div class="bubble15"></div>
        <div class="bubble16"></div>
        <div class="bubble17"></div>
        <div class="bubble18"></div>
        <div class="bubble19"></div>
        <div class="bubble20"></div>
        <div class="bubble21"></div>
    </footer>


    <script defer="defer" src="../Nav_Bar/Nav_Bar.js"></script>
</body>

</html>
````
