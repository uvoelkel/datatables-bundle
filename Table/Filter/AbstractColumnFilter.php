<?php

namespace Voelkel\DataTablesBundle\Table\Filter;

abstract class AbstractColumnFilter
{
    public $options = [];

    /** @var null|\Symfony\Component\DependencyInjection\ContainerInterface */
    protected $container;

    public function setOptions(array $options): self
    {
        $this->options = array_merge($this->options, $this->getDefaultOptions(), $options);

        return $this;
    }

    /**
     * @param null|\Symfony\Component\DependencyInjection\ContainerInterface $container
     * @return $this
     */
    public function setContainer($container): self
    {
        $this->container = $container;

        return $this;
    }

    public function getBlockPrefix()
    {
        return self::fqcnToBlockPrefix(get_class($this));
    }

    public function getParent(): ?string
    {
        return null;
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $qb
     * @param string $field
     * @param string $parameter
     * @param string $value
     */
    abstract public function buildQuery(\Doctrine\ORM\QueryBuilder $qb, $field, $parameter, $value): void;

    abstract protected function getDefaultOptions(): array;

    // stolen from \Symfony\Component\Form\Util\StringUtil
    public static function fqcnToBlockPrefix($fqcn)
    {
        // Non-greedy ("+?") to match "type" suffix, if present
        if (preg_match('~([^\\\\]+?)(type)?$~i', $fqcn, $matches)) {
            return strtolower(preg_replace(array('/([A-Z]+)([A-Z][a-z])/', '/([a-z\d])([A-Z])/'), array('\\1_\\2', '\\1_\\2'), $matches[1]));
        }
    }
}
