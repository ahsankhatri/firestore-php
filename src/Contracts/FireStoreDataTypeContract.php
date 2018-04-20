<?php

namespace MrShan0\PHPFirestore\Contracts;

interface FireStoreDataTypeContract
{
    /**
     * Set data into variable which will be processed later
     *
     * @return mixed
     */
    public function setData($data);

    /**
     * Get stored data.
     *
     * @return mixed
     */
    public function getData();

    /**
     * Get parsed value of any particular field.
     *
     * @return mixed
     */
    public function parseValue();
}
