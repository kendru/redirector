<?php

/* index.html */
class __TwigTemplate_bdc97d2b8ee63bbd72a3a6991eb09517 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        echo "<html>
    <head>
        <meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\">
    
        <title>Test Page</title>
    </head>
    <body>
        <h1>Welcome</h1>
        <p>Welcome to the test page!</p>
        <em>You said: ";
        // line 10
        echo twig_escape_filter($this->env, (isset($context["hello"]) ? $context["hello"] : null), "html", null, true);
        echo "</em>
    </body>
</html>
";
    }

    public function getTemplateName()
    {
        return "index.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  30 => 10,  19 => 1,);
    }
}
