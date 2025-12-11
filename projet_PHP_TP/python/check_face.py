import sys
import os
import json
import logging
from deepface import DeepFace

# Désactiver logs TensorFlow + DeepFace
logging.getLogger("deepface").setLevel(logging.ERROR)
os.environ["TF_CPP_MIN_LOG_LEVEL"] = "3"

DB_PATH = os.path.join(os.path.dirname(__file__), "..", "Assets", "Images", "known")

def main():
    if len(sys.argv) < 2:
        print(json.dumps({"status": "error", "msg": "Aucune image transmise"}))
        sys.exit(1)

    probe_path = sys.argv[1]

    if not os.path.exists(probe_path):
        print(json.dumps({"status": "error", "msg": "Fichier introuvable"}))
        sys.exit(1)

    # Supprimer fichiers .pkl
    pkls = [
        "ds_model_vggface_detector_opencv_aligned_normalization_base_expand_0.pkl",
        "representations_vgg_face.pkl",
        "representations.pkl"
    ]

    for pkl in pkls:
        pkl_path = os.path.join(DB_PATH, pkl)
        if os.path.exists(pkl_path):
            try:
                os.remove(pkl_path)
            except Exception as e:
                print(json.dumps({"status": "error", "msg": f"Impossible de supprimer {pkl}: {str(e)}"}))
                sys.exit(1)

    try:
        # --- LIGNE CRUCIALE POUR ÉVITER LES EMOJIS ---
        sys.stdout.reconfigure(encoding='utf-8')

        result = DeepFace.find(
            img_path=probe_path,
            db_path=DB_PATH,
            enforce_detection=False
        )

        if len(result) > 0 and not result[0].empty:
            match = result[0].iloc[0]
            print(json.dumps({
                "status": "found",
                "match": os.path.basename(match["identity"]),
                "distance": match["distance"]
            }))
        else:
            print(json.dumps({"status": "not_found"}))

    except Exception as e:
        print(json.dumps({"status": "error", "msg": str(e)}))

if __name__ == "__main__":
    main()
