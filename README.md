# PHP Statistics Library
by [Keith Hatfield](http://keithscode.com)

A simple statistics library.
Originally built for use as a CodeIgniter library, but also functions as a standalone class

## Available Data
Currently allows you to retrieve:
* Mean (Average)
* Median
* Standard Deviation (Sample)
* Variance (Sample)
* Standard Deviation (Population)
* Variance (Population)
* Most Frequent Set Items
* Least Frequestn Set Items
* Frequecy of a set item (as count or percentage)

This is a basic analysis tool. As time permits, more functionality will be added

## Installation

###To use in a standalone project:
Copy the `statistics.php` file into your project

    include('statistics.php');
    $statistics = new Statistics();
    
###To use in CodeIgniter:
Place the `statistics.php` file in you `application/libraries/` directory

    $this->load->library('statistics');
    //funcationality can be accessed via $this->statistics
    
## Usage

### Properties
These properties can be retrieved in one of the ways noted below

    std_deviation (can also be retrieved as std_dev)
    pop_std_deviation (can also be retrieved as pop_std_dev)
    variance
    pop_variance
    mean (can also be retrieved as average)
    median

### Adding a dataset
    
    $dataset = array(1, 1, 1, 2, 2, 3, 3, 4, 4, 5, 5, 5, 6, 7, 7, 8, 8, 9, 9, 10, 10);
    $statistics->addSet($dataset);
    
### Getting the analysis data
Because people like to do things different ways, most of the data can be accessed three different ways

    //using camelCased get methods
    $std_dev = $statistics->getStdDeviation();
    
    //using underscore get methods
    $std_dev = $statistics->get_std_deviation();
    
    //using the property name
    $std_dev = $statistics->std_deviation;

Other methods of note

    //get the dataset
    $set = $statistics->getSet()
    
    //get the most frequent item(s)
    $most = $statistics->getMostFrequent();
    //in the above sample dataset, 1 and 5 occurr 3 times each, so this would return:
    Array
    (
        [3] => Array
            (
                [0] => 1
                [1] => 5
            )
    
    )
    
    //get the least frequent item(s)
    $least = $statistics->getLeastFrequent();
    //in the above sample dataset, 10 occurrs 1 time, so this would return:
    Array
    (
        [1] => Array
            (
                [0] => 10
            )
    
    )
    
    //get the frequency of a set item as count and percentage
    $count = $statistics->getFrequency(4); //returns 2
    $pct   = $statistics->getFrequency(4, true); //returns 0.095238095238095