import os
import requests

# Create the uploads directory if it doesn't exist
os.makedirs("src/uploads", exist_ok=True)

# Base URL for the Dog CEO API
BASE_URL = "https://dog.ceo/api/breeds/image/random"

# Number of images to download
NUM_IMAGES = 20

for i in range(1, NUM_IMAGES + 1):
    # Fetch a random dog image URL
    response = requests.get(BASE_URL)
    if response.status_code == 200:
        data = response.json()
        image_url = data["message"]

        # Download the image
        image_response = requests.get(image_url)
        if image_response.status_code == 200:
            # Save the image to the uploads folder
            file_name = f"src/uploads/dog{i}.jpg"
            with open(file_name, "wb") as file:
                file.write(image_response.content)
            print(f"Downloaded {file_name}")
        else:
            print(f"Failed to download image {i}")
    else:
        print(f"Failed to fetch image URL for image {i}")