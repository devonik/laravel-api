
import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler, useRef, useState } from 'react';
import InputLabel from '../../components/InputLabel';
import TextInput from '../../components/TextInput';
import InputError from '../../components/InputError';
import PrimaryButton from '@/components/PrimaryButton';
import axios from 'axios';

export default function Edit() {
    const [apiResponse, setApiResponse] = useState({});
    const nameInput = useRef<HTMLInputElement>(null);
    const types = [{ text: 'Image', value: 'img' }, { text: 'Christmas card', value: 'card' }]

    const {
        data,
        setData,
        post,
        processing,
        reset,
        errors,
    } = useForm({
        name: '',
        resolutionHeight: 50,
        type: 'img'
    });

    const onSubmit: FormEventHandler = (e) => {
        e.preventDefault();

        axios.post(`/api/yogagraphy/getImageByForm/`, data).then(response => {
            setApiResponse(response.data)
        })
        .catch(function (error) {
            setApiResponse(error);
        });
    }

    return (
        <section>
            <header>
                <h2 className="text-lg font-medium text-gray-900 dark:text-gray-100">Yogagraphy</h2>

                <p className="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    This is my yogagraphy project
                </p>
            </header>
                <form onSubmit={onSubmit} className="p-6">
                    <h2 className="text-lg font-medium text-gray-900 dark:text-gray-100">
                        Type in your name to generate a Yogagraphy
                    </h2>

                    <p className="mt-1 text-sm text-gray-600 dark:text-gray-400">
                       You can either generate the yogagraphy only or within a christmas card
                    </p>

                    <div className="mt-6">
                        <InputLabel htmlFor="type" value="type" className="sr-only" />
                        <select id="type" name="type" onChange={(e) => setData('type', e.target.value)}>
                            {types.map((type) => (<option key={type.value} value={type.value}>{type.text}</option>))}
                        </select>

                        {data.type === 'img' && (<div style={{marginTop: 5}}>
                            <InputLabel htmlFor="resolutionHeight">Choose the resolution (px) of each yogagraphy item (width and height will be equal). You can use &uarr; or &darr; on your keyboard</InputLabel>
                            <TextInput id="resolutionHeight" name="resolutionHeight" value={data.resolutionHeight} type="number" min="50" max="1500" step="10" required onChange={(e) => setData('resolutionHeight', e.target.value)}></TextInput>
                        </div>)}

                        <InputLabel htmlFor="name" value="name" className="sr-only" />

                        <TextInput
                            id="name"
                            type="text"
                            name="name"
                            ref={nameInput}
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            className="mt-1 block w-3/4"
                            isFocused
                            placeholder="Name"
                        />

                        <InputError message={errors.name} className="mt-2" />
                    </div>

                    <div className="mt-6 flex justify-end">

                        <PrimaryButton className="ms-3" disabled={!data.name || processing}>
                            Generate
                        </PrimaryButton>
                    </div>
                </form>
                {apiResponse && apiResponse.hasOwnProperty('image') && (
                    <img className='m-auto' src={apiResponse.image.encoded} />
                )}
        </section>
    );
}
