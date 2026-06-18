import api from '@/javascript/api/api'

export default{
    async add_media(file, file_name, folder_id){
        const formData = new FormData();
        formData.append('file', file);
        formData.append('file_name', file_name);
        formData.append('folder_id', folder_id);

        const response = await api.post('/medias', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });
        return response;
    },

    async get_medias() {
        const response = await api.get(
            '/medias'
        )
        return response
    },

    async get_children_directory(directory_id) {
        const response = await api.get(
            `/medias/directory/${directory_id}`
        )
        return response
    },

    async create_directory(directory_name, parent_id) {
        const response = await api.post('/medias/directory', {
            directory_name: directory_name,
            parent_id: parent_id
        });
        return response;
    },

    async get_thumbnails_medias(urls) {
        const response = await api.get('/medias/thumbnails', {
                params: { "urls": urls }
        });
        return response;
    },

    async delete_media(file_id) {
        const response = await api.delete(
            `/medias/${file_id}`,
        )
        return response
    }
}