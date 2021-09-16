<?

class Fruit
{
    private $name;
    private $color;

    function __construct($n)
    {
        $this->name = $n;
    }

    public function getName()
    {
        return $this->name;
    }
}


?>