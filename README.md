# Paper Trail External Module

The goal of this external module (ext-mod) is to streamline the creation of pdf files in REDCap when it needs to be automatically attached to an upload field in the same project. 
It can create a pdf-version of a pre-determined REDCap instrument, when specific fields have been completed. 
You have full control over which fields must be completed before the pdf file is created, as well as selecting the desired upload field.
Additionally, it also contains a feature that allows you to control when to attach the pdf-file to an upload field by answering one single Yes/No field. 
Finally, the ext-mod wouldn't be complete without a feature to remove the label "remove file and send it" from the upload field. 
The following steps outline how the ext-mod is meant to be used: 

##Using the drop down fields in the ext-mod configuration window, specify:
1. Name of the instrument expected to be saved in pdf format
2. Upload (target) field receiving the pdf file from step #1
3. Prefix of pdf file name from step #2; it will be concatenated to system constants i.e. prefix_project_id_record_id_date_time.pdf
4. Type of upload:
4. a) Automatic file upload after all specified fields have been answered
4. b) Controlled by a single Yes/No field triggering the upload
5. c) Disable configuration for resetting selection in case troubleshooting is needed
6. Hide the "Remove File" and "Send it" from Target Upload Field

## Future Enhancements:
1. Provide control over which link are hidden in the upload fields
2. Provide control over the state in which the target field instrument is set
3. Add functionality to allow more than one target field

## Comments, questions, or concerns, please contact:
Ed Morales at EMORALES@BWH.HARVARD.EDU