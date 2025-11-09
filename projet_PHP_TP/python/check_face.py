import sys
import os
import json
from deepface import DeepFace

# Dossier contenant les images enregistrées
DB_PATH = os.path.join(os.path.dirname(__file__), "..", "Assets", "Images", "known")

def main():
    if len(sys.argv) < 2:
        print(json.dumps({"status": "error", "msg": "Aucune image transmise"}))
        sys.exit(1)

    probe_path = sys.argv[1]

    if not os.path.exists(probe_path):
        print(json.dumps({"status": "error", "msg": "Fichier introuvable"}))
        sys.exit(1)

    try:
        # Recherche de correspondance
        result = DeepFace.find(img_path=probe_path, db_path=DB_PATH, enforce_detection=False)

        if len(result) > 0 and not result[0].empty:
            match = result[0].iloc[0]
            identity = os.path.basename(match["identity"])
            distance = match["distance"]

            print(json.dumps({
                "status": "found",
                "match": identity,
                "distance": distance
            }))
        else:
            print(json.dumps({"status": "not_found"}))

    except Exception as e:
        print(json.dumps({"status": "error", "msg": str(e)}))

if __name__ == "__main__":
    main()
