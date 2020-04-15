
    /**
     * <{$methodName}>
     *
<{$paramsStatement}>
     * @param mixed &$ret  result
     *
     * @return mixed
     */
    public function <{$methodName}>(<{$params}>, &$ret = false)
    {
        $path = <{$path}>;
        $req = new Request();
        $this->setReq($req, '<{$method}>', $path);
        <{$requestBody}>
        return $this->request(__FUNCTION__, $req, $ret);
    }