# PSYC201A Replication Project

This is a copy of the GitHub template to use for replications projects in PSYCH 201a at UCSD. New repo is created using this template under the name Nummenmaa2013.

This project aims to replicate experiment 1c from Numenmaa et al. (2013). See 'Replication_Report' in the 'writeup' folder for the description of procedures, codes, and the result. Data is stored in the 'data' folder. 'qualtrics_source' and 'website_code' contains the qsf file and the php code (adapted from [this](https://version.aalto.fi/gitlab/eglerean/embody/-/tree/master/v1?ref_type=heads).) for the web experiment.

## Data types and structures
The data format of this project includes survey data (.csv) , raw painting data (pixel coordinates), and the processed pixel coordinates of the drawing data for BSMs (.mat). 'data' folder includes data from pilot A, pilot B, and the data from the main experiment, as well as a folder containing some files needed for preprocessing. For each folder ('pilot A', 'pilot B', 'final_data'), the survey data, raw drawing data, and the processed drawing data is stored in separate subfolders.

### Drawing data (pixel coordinates)
If you follow the code written in 'Replication_Report', you should be able to load and process the raw data. Please refer to the 'website_code' folder to get more sense of how the raw coordinates are stored for each emotion. 

### Survey metadata
Survey data includes some demographics as well as language backgrounds. Please refer to the csv files from pilot A and pilot B, or .qsf file in 'qualtrics_source' folder to get a sense of what specific questions were asked for each question code. For the survey data from the main experiment, only the rows containing question codes were preserved in the process of reassigning anonymous participant codes. 

| Variable Name           | Notes                  |
| ----------------------- | ---------------------- |
| `finished`              | 0: "No", 1: "Yes"      |
| `gender`                | 1: "Male", 2: "Female", 3: "Others" |
| `LEAPQ_edu_level`       | 1: "Less than High School", 2: "High School", 3: "Professional Training", 4: "Some College", 5: "College", 6: "Some Graduate School", 7: "Masters", 8: "Ph.D./M.D./J.D.", 9: "Other" |
| `Language_Kor`          | 1: "Yes", 2: "No" |
| `Korean_heritage`       | 1: "Yes", 2: "No" |